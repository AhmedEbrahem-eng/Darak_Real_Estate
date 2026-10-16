<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_dashboard_overview(): void
    {
        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_dashboard_overview(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'You are not allowed to access this page']);
    }

    public function test_admin_user_can_access_dashboard_overview(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'users',
                     'properties',
                     'property_types',
                     'user_reports',
                     'property_reports',
                     'reviews',
                     'amenities',
                 ]);
    }
}
