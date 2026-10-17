<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserListingSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_user_list()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_user_list()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_user_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/users');

        $response->assertStatus(200);
    }
}
