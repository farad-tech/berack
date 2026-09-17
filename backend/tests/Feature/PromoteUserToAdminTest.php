<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoteUserToAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_promotes_an_existing_user_to_admin(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'is_admin' => false,
        ]);

        $this->artisan('user:promote-admin owner@example.com')
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }
}
