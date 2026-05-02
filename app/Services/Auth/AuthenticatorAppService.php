<?php

namespace App\Services\Auth;

use App\Models\User;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Str;

class AuthenticatorAppService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        $count = max(1, (int) config('two_factor.authenticator.recovery_codes', 8));
        $codes = [];

        for ($index = 0; $index < $count; $index++) {
            $code = substr($this->base32Encode(random_bytes(8)), 0, 10);
            $codes[] = substr($code, 0, 5).'-'.substr($code, 5);
        }

        return $codes;
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (! $user->hasTwoFactorEnabled()) {
            return false;
        }

        return $this->verifyCodeForSecret((string) $user->two_factor_secret, $code);
    }

    public function verifyCodeForSecret(string $secret, string $code, ?int $time = null): bool
    {
        $code = $this->normalizeTotpCode($code);
        $digits = $this->digits();

        if (! preg_match('/^\d{'.$digits.'}$/', $code)) {
            return false;
        }

        $time ??= time();
        $period = $this->period();
        $window = max(0, (int) config('two_factor.authenticator.window', 1));
        $counter = intdiv($time, $period);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->codeForCounter($secret, $counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    public function currentCodeForSecret(string $secret, ?int $time = null): string
    {
        return $this->codeForCounter($secret, intdiv($time ?? time(), $this->period()));
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $submitted = $this->normalizeRecoveryCode($code);
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $storedCode) {
            if (hash_equals($this->normalizeRecoveryCode((string) $storedCode), $submitted)) {
                unset($codes[$index]);

                $user->forceFill([
                    'two_factor_recovery_codes' => array_values($codes),
                ])->save();

                return true;
            }
        }

        return false;
    }

    public function otpauthUrl(User $user, string $secret): string
    {
        $issuer = (string) config('two_factor.authenticator.issuer', config('app.name', 'DentalCare'));
        $label = rawurlencode($issuer).':'.rawurlencode($user->email);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => $this->digits(),
            'period' => $this->period(),
        ], '', '&', PHP_QUERY_RFC3986);

        return 'otpauth://totp/'.$label.'?'.$query;
    }

    public function qrCodeSvg(string $content, int $size = 220): string
    {
        $matrix = Encoder::encode($content, ErrorCorrectionLevel::M())->getMatrix();
        $margin = 4;
        $matrixSize = $matrix->getWidth();
        $viewBoxSize = $matrixSize + ($margin * 2);
        $path = [];

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $path[] = 'M'.($x + $margin).' '.($y + $margin).'h1v1h-1z';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$viewBoxSize.' '.$viewBoxSize.'" role="img" aria-label="QR code 2FA">'
            .'<rect width="100%" height="100%" fill="#ffffff"/>'
            .'<path d="'.implode('', $path).'" fill="#111827"/>'
            .'</svg>';
    }

    private function codeForCounter(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $binaryCounter = pack('N2', intdiv($counter, 4294967296), $counter % 4294967296);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($truncated % (10 ** $this->digits())), $this->digits(), '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $bits = '';
        $encoded = '';

        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    private function base32Decode(string $secret): string
    {
        $secret = Str::upper(preg_replace('/[^A-Z2-7]/i', '', $secret) ?? '');
        $bits = '';
        $decoded = '';

        foreach (str_split($secret) as $character) {
            $value = strpos(self::BASE32_ALPHABET, $character);

            if ($value === false) {
                continue;
            }

            $bits .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
        }

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }

    private function normalizeTotpCode(string $code): string
    {
        return preg_replace('/\s+/', '', $code) ?? '';
    }

    private function normalizeRecoveryCode(string $code): string
    {
        return Str::upper(preg_replace('/[^A-Z2-7]/i', '', $code) ?? '');
    }

    private function digits(): int
    {
        return max(6, min(8, (int) config('two_factor.authenticator.digits', 6)));
    }

    private function period(): int
    {
        return max(15, (int) config('two_factor.authenticator.period', 30));
    }
}
