<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Amenity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityAdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that public read-only routes for amenities are accessible to guests.
     */
    public function test_amenities_read_only_routes_are_accessible_to_guests(): void
    {
        $amenity = Amenity::factory()->create([
            'name' => 'Swimming Pool',
            'slug' => 'swimming-pool'
        ]);

        $this->getJson('/api/amenities')
            ->assertStatus(200);

        $this->getJson('/api/amenities/available-amenities')
            ->assertStatus(200);

        $this->getJson('/api/amenities/swimming-pool')
            ->assertStatus(200);
    }

    /**
     * Test that dashboard overview is protected from guests and non-admins.
     */
    public function test_dashboard_overview_is_protected(): void
    {
        // 1. Guest access should fail
        $this->getJson('/api/dashboard/home')
            ->assertStatus(401);

        // 2. Standard user access should fail with forbidden (403)
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/home')
            ->assertStatus(403);

        // 3. Admin access should succeed (200)
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $this->getJson('/api/dashboard/home')
            ->assertStatus(200);
    }

    /**
     * Test that creating an amenity is protected from guests and non-admins.
     */
    public function test_store_amenity_is_protected(): void
    {
        $payload = [
            'name' => 'Gym',
            'slug' => 'gym',
        ];

        // 1. Guest
        $this->postJson('/api/amenities', $payload)
            ->assertStatus(401);

        // 2. Non-admin user
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $this->postJson('/api/amenities', $payload)
            ->assertStatus(403);

        // 3. Admin user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->postJson('/api/amenities', $payload)
            ->assertStatus(201);
    }

    /**
     * Test that updating an amenity is protected.
     */
    public function test_update_amenity_is_protected(): void
    {
        $amenity = Amenity::factory()->create([
            'name' => 'Garden',
            'slug' => 'garden'
        ]);

        $payload = [
            'name' => 'Updated Garden',
            'slug' => 'garden'
        ];

        // 1. Guest
        $this->putJson('/api/amenities/garden', $payload)
            ->assertStatus(401);

        // 2. Non-admin user
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $this->putJson('/api/amenities/garden', $payload)
            ->assertStatus(403);

        // 3. Admin user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->putJson('/api/amenities/garden', $payload)
            ->assertStatus(200);
    }

    /**
     * Test that updating amenity availability is protected.
     */
    public function test_update_amenity_availability_is_protected(): void
    {
        $amenity = Amenity::factory()->create([
            'name' => 'Parking',
            'slug' => 'parking'
        ]);

        $payload = [
            'status' => 'available'
        ];

        // 1. Guest
        $this->putJson("/api/amenities/availability/{$amenity->id}", $payload)
            ->assertStatus(401);

        // 2. Non-admin user
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $this->putJson("/api/amenities/availability/{$amenity->id}", $payload)
            ->assertStatus(403);

        // 3. Admin user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->putJson("/api/amenities/availability/{$amenity->id}", $payload)
            ->assertStatus(200);
    }

    /**
     * Test that deleting an amenity is protected.
     */
    public function test_delete_amenity_is_protected(): void
    {
        $amenity = Amenity::factory()->create([
            'name' => 'Elevator',
            'slug' => 'elevator'
        ]);

        // 1. Guest
        $this->deleteJson('/api/amenities/elevator')
            ->assertStatus(401);

        // 2. Non-admin user
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $this->deleteJson('/api/amenities/elevator')
            ->assertStatus(403);

        // 3. Admin user
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->deleteJson('/api/amenities/elevator')
            ->assertStatus(200);
    }
}
