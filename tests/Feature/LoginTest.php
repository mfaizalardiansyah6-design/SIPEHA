<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_using_email(): void
    {
        $user = User::factory()->create([
            'nip' => '198708070001',
            'email' => 'petugas@simhak.test',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'identity' => 'petugas@simhak.test',
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_using_nip(): void
    {
        $user = User::factory()->create([
            'nip' => '198708070002',
            'email' => 'petugas2@simhak.test',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'identity' => '198708070002',
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'nip' => '198708070003',
            'email' => 'petugas3@simhak.test',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'identity' => '198708070003',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('identity');

        $this->assertGuest();
    }
}
