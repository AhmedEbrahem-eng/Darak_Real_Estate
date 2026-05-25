<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdministrativeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_list_user_reports()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-users');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(200);
    }
}
