<?php


namespace Tests\Feature\LMS\Auth\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use LMS\Auth\Mail\SendGreetings;
use LMS\User\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->seed();
    }

    public function testUserCanRegister()
    {
        // Prepare
        Mail::fake();
        $payload = [
            'name' => 'daniel corazon',
            'username' => 'danielhe4rt',
            'email' => 'hey@danielheart.dev',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ];
        config(['app.env' => 'production']);

        // Act
        $response = $this->post(route('auth-register'), $payload);
        // Assert
        Mail::assertSent(SendGreetings::class);

        $response->assertCreated();
        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'username' => $payload['username'],
        ]);
    }

    public function testUserCanAuthenticate()
    {
        // Prepare
        $user = User::factory()->create();
        $payload = [
            'email' => $user->email,
            'password' => 'secret'
        ];

        // Act
        $response = $this->post(route('auth-login'), $payload);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'last_seen' => Carbon::now()
        ]);
    }

    public function testUserShouldNotAuthenticateWithInvalidCredentials()
    {
        // Prepare
        $user = User::factory()->create();
        $payload = [
            'email' => $user->email,
            'password' => 'he4rtlessftw123'
        ];

        // Act
        $response = $this->post(route('auth-login'), $payload);

        // Assert
        $response->assertUnauthorized();
    }

    public function testUserCanLogoutFromApp()
    {
        // Prepare
        $user = User::factory()->create();

        // Act
        $this->actingAs($user);
        $response = $this->get(route('auth-logout'));

        // Assert
        $response->assertRedirect('/');
    }

    public function testUserShouldBeRedirectedUponUnauthenticatedLogoutAttempt()
    {
        // Act
        $response = $this->get(route('auth-logout'));

        // Assert
        $response->assertRedirect(route('login'));
    }
}
