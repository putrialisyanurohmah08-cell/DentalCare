<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Auth\AuthenticatorAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_enable_authenticator_two_factor(): void
    {
        $user = User::factory()->create();
        $authenticator = app(AuthenticatorAppService::class);

        $response = $this
            ->actingAs($user)
            ->get(route('profile.two-factor.setup'));

        $response->assertOk();
        $response->assertSee('<svg', false);

        $secret = session('auth.two_factor_setup_secret');
        $this->assertNotEmpty($secret);

        $response = $this
            ->actingAs($user)
            ->post(route('profile.two-factor.enable'), [
                'code' => $authenticator->currentCodeForSecret($secret),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', absolute: false));

        $user->refresh();

        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertCount(8, $user->two_factor_recovery_codes);
    }

    public function test_user_can_regenerate_recovery_codes(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactor($user, ['ABCDE-FGHIJ']);

        $response = $this
            ->actingAs($user)
            ->post(route('profile.two-factor.recovery-codes'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', absolute: false));

        $this->assertCount(8, $user->refresh()->two_factor_recovery_codes);
        $this->assertNotContains('ABCDE-FGHIJ', $user->two_factor_recovery_codes);
    }

    public function test_user_can_disable_two_factor(): void
    {
        $user = User::factory()->create();
        $this->enableTwoFactor($user);

        $response = $this
            ->actingAs($user)
            ->delete(route('profile.two-factor.disable'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit', absolute: false));

        $user->refresh();

        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull(User::query()->find($user->id));
        $this->assertSame(1, User::withDeleted()->findOrFail($user->id)->IsDeleted);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    /**
     * @param array<int, string> $recoveryCodes
     */
    private function enableTwoFactor(User $user, array $recoveryCodes = ['ABCDE-FGHIJ']): void
    {
        $authenticator = app(AuthenticatorAppService::class);

        $user->forceFill([
            'two_factor_secret' => $authenticator->generateSecret(),
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ])->save();
    }
}
