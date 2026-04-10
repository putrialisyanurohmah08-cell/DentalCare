<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.google.client_id', 'google-client-id');
        config()->set('services.google.client_secret', 'google-client-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');
    }

    public function test_users_can_be_redirected_to_google(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_google_redirect_requires_configuration(): void
    {
        config()->set('services.google.client_id', '');
        config()->set('services.google.client_secret', '');
        config()->set('services.google.redirect', '');

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Konfigurasi login Google belum lengkap.');
    }

    public function test_google_callback_requires_configuration(): void
    {
        config()->set('services.google.client_id', '');
        config()->set('services.google.client_secret', '');
        config()->set('services.google.redirect', '');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Konfigurasi login Google belum lengkap.');
    }

    public function test_new_users_can_register_and_login_with_google(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->fakeGoogleUser(
                id: 'google-123',
                name: 'Putri Alisha',
                email: 'putri@example.com',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $user = User::query()->firstWhere('email', 'putri@example.com');

        $this->assertNotNull($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame(UserRole::Patient, $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_existing_users_are_linked_to_google_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'putri@example.com',
            'google_id' => null,
            'google_avatar' => null,
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')
            ->once()
            ->andReturn($this->fakeGoogleUser(
                id: 'google-456',
                name: 'Putri Alisha',
                email: 'putri@example.com',
                avatar: 'https://example.com/avatar.png',
            ));

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $user->refresh();

        $this->assertSame('google-456', $user->google_id);
        $this->assertSame('https://example.com/avatar.png', $user->google_avatar);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    private function fakeGoogleUser(
        string $id,
        string $name,
        string $email,
        ?string $avatar = 'https://example.com/google-avatar.png',
    ): SocialiteUser {
        $user = new SocialiteUser();

        $user->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
        ]);

        return $user;
    }
}
