<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmenitySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_mutate_amenities(): void
    {
        $amenity = Amenity::create([
            'name' => 'WiFi',
            'availability' => 'available',
        ]);

        $this->postJson('/api/amenities', [
            'name' => 'Pool',
        ])->assertStatus(401);

        $this->putJson('/api/amenities/wifi', [
            'name' => 'High Speed WiFi',
        ])->assertStatus(401);

        $this->putJson("/api/amenities/availability/{$amenity->id}", [
            'status' => 'unavailable',
        ])->assertStatus(401);

        $this->deleteJson('/api/amenities/wifi')->assertStatus(401);
    }

    public function test_non_admin_users_cannot_mutate_amenities(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $amenity = Amenity::create([
            'name' => 'WiFi',
            'availability' => 'available',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/amenities', [
                'name' => 'Pool',
            ])->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/amenities/wifi', [
                'name' => 'High Speed WiFi',
            ])->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/amenities/availability/{$amenity->id}", [
                'status' => 'unavailable',
            ])->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/amenities/wifi')->assertStatus(403);
    }

    public function test_admin_users_can_mutate_amenities(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/amenities', [
                'name' => 'Gym',
            ]);

        $response->assertStatus(201);

        $amenity = Amenity::where('slug', 'gym')->first();
        $this->assertNotNull($amenity);

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/amenities/gym', [
                'name' => 'Luxury Gym',
            ])->assertStatus(200);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/amenities/availability/{$amenity->id}", [
                'status' => 'unavailable',
            ])->assertStatus(200);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/amenities/luxury-gym')
            ->assertStatus(200);
    }
}
