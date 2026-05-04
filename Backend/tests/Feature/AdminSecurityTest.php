<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cannot_access_admin_routes()
    {
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->getJson('/api/dashboard/home')->assertStatus(401);
    }

    /** @test */
    public function public_routes_are_accessible_unauthenticated()
    {
        $this->getJson('/api/amenities')->assertStatus(200);
        $this->getJson('/api/property-types')->assertStatus(200);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-users')->assertStatus(403);
        $this->getJson('/api/report-properties')->assertStatus(403);
        $this->getJson('/api/dashboard/home')->assertStatus(403);
    }

    /** @test */
    public function admins_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // index() in ReportUserController returns 400 if empty
        $this->getJson('/api/report-users')->assertStatus(400);
        $this->getJson('/api/report-properties')->assertStatus(400);
        $this->getJson('/api/amenities')->assertStatus(200);
    }
}
