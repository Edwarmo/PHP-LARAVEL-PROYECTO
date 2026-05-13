<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    #[Test]
    public function show_returns_login_page(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    #[Test]
    public function store_with_valid_credentials_redirects(): void
    {
        $response = $this->post(route('login'), [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect('/');
        $this->assertTrue(Auth::check());
    }

    #[Test]
    public function store_with_admin_email_redirects_to_admin(): void
    {
        $admin = User::factory()->create([
            'email'    => 'admin@videoconfreservas.com',
            'password' => bcrypt('adminpass'),
        ]);
        $response = $this->post(route('login'), [
            'email'    => 'admin@videoconfreservas.com',
            'password' => 'adminpass',
        ]);
        $response->assertRedirect('/admin');
        $this->assertTrue(Auth::check());
    }

    #[Test]
    public function store_with_invalid_credentials_returns_errors(): void
    {
        $response = $this->post(route('login'), [
            'email'    => 'user@example.com',
            'password' => 'wrong-password',
        ]);
        $response->assertSessionHasErrors(['email']);
        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $response = $this->post(route('login'), []);
        $response->assertSessionHasErrors(['email', 'password']);
    }

    #[Test]
    public function destroy_logs_out_and_redirects(): void
    {
        $this->actingAs($this->user);
        $response = $this->post(route('logout'));
        $response->assertRedirect('/');
        $this->assertFalse(Auth::check());
    }

    #[Test]
    public function show_is_blocked_for_authenticated_users(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('login'));
        $response->assertRedirect();
    }
}
