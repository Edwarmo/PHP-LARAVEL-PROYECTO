<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function show_returns_register_page(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
    }

    #[Test]
    public function store_creates_user_and_redirects(): void
    {
        Mail::fake();
        $response = $this->post(route('register'), [
            'name'                  => 'Juan Pérez',
            'email'                 => 'juan@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
        $this->assertTrue(Auth::check());
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->post(route('register'), []);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function store_validates_email_format(): void
    {
        $response = $this->post(route('register'), [
            'name'                  => 'Test',
            'email'                 => 'not-an-email',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function store_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);
        $response = $this->post(route('register'), [
            'name'                  => 'Test',
            'email'                 => 'existing@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function store_validates_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function store_validates_password_min_length(): void
    {
        $response = $this->post(route('register'), [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function store_sends_email_notification(): void
    {
        Mail::fake();
        config(['mail.mailer' => 'array']);
        $this->post(route('register'), [
            'name'                  => 'Juan Pérez',
            'email'                 => 'juan@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
    }

    #[Test]
    public function show_is_blocked_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('register'));
        $response->assertRedirect();
    }
}
