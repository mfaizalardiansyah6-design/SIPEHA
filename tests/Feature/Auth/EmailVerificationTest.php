<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_is_not_available(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertNotFound();
    }

    public function test_email_verification_route_is_not_defined(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/email/verify/'.$user->id.'/hash');

        $response->assertNotFound();
    }
}
