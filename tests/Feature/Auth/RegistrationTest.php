<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk()->assertSee('Имя пользователя');
    }

    public function test_new_users_can_register_without_becoming_admins(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'username' => 'test-user',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => true,
        ])->assertSessionHasNoErrors()->assertRedirect(route('albums.index', absolute: false));

        $user = User::whereUsername('test-user')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->is_admin);
    }

    public function test_username_must_be_unique_and_valid_for_urls(): void
    {
        User::factory()->create(['username' => 'taken']);
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $this->post('/register', $data + ['username' => 'taken'])->assertSessionHasErrors('username');
        $this->post('/register', $data + ['username' => '123'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
