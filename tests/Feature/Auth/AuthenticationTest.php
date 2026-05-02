<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Auth\AuthenticatorAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        config()->set('services.google.client_id', '');
        config()->set('services.google.client_secret', '');
        config()->set('services.google.redirect', '');

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk dengan Google belum aktif di environment ini.', escape: false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_doctors_are_redirected_to_their_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Doctor,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('doctor.dashboard', absolute: false));
    }

    public function test_patient_login_keeps_booking_state_on_the_home_page(): void
    {
        $user = User::factory()->create();
        $target = route('home', [
            'doctor_id' => 5,
            'service_id' => 2,
            'booking_date' => '2026-04-20',
        ], absolute: false).'#booking-section';

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'redirect' => $target,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect($target);
    }

    public function test_users_with_two_factor_enabled_must_verify_an_authenticator_code(): void
    {
        $user = User::factory()->create();
        $code = $this->enableTwoFactorAndGetCode($user);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('two-factor.login', absolute: false));

        $response = $this->post(route('two-factor.verify'), [
            'code' => $code,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_finish_two_factor_login_with_a_recovery_code(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactorAndGetCode($user, ['ABCDE-FGHIJ']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login', absolute: false));

        $response = $this->post(route('two-factor.verify'), [
            'code' => 'ABCDE-FGHIJ',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('home', absolute: false));
        $this->assertSame([], $user->refresh()->two_factor_recovery_codes);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_cannot_finish_login_with_invalid_two_factor_code(): void
    {
        $user = User::factory()->create();
        $validCode = $this->enableTwoFactorAndGetCode($user);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post(route('two-factor.verify'), [
            'code' => $validCode === '000000' ? '111111' : '000000',
        ])->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_two_factor_screen_requires_a_pending_challenge(): void
    {
        $this->get(route('two-factor.login'))
            ->assertRedirect(route('login'));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * @param array<int, string> $recoveryCodes
     */
    private function enableTwoFactorAndGetCode(User $user, array $recoveryCodes = ['ABCDE-FGHIJ']): string
    {
        $authenticator = app(AuthenticatorAppService::class);
        $secret = $authenticator->generateSecret();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $authenticator->currentCodeForSecret($secret);
    }
}
