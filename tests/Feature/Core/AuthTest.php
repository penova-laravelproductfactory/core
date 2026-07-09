<?php

namespace Tests\Feature\Core;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Core\Auth — minimal smoke tests. Each Core module grows its own
 * test class under tests/Feature/Core as behaviour is added.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_users_can_authenticate(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/workspace');
    }

    public function test_guests_are_redirected_from_the_panel(): void
    {
        $this->get('/workspace')->assertRedirect('/login');
    }
}
