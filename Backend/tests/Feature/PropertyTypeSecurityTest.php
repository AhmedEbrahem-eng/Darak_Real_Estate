<?php

namespace Tests\Feature;

use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyTypeSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_create_property_type(): void
    {
        $response = $this->postJson('/api/property-types', [
            'name' => 'Apartment',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_create_property_type(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/property-types', [
            'name' => 'Apartment',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_user_can_create_property_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/property-types', [
            'name' => 'Apartment',
            'slug' => 'apartment',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('property_types', [
            'name' => 'Apartment',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_property_type(): void
    {
        $propertyType = PropertyType::factory()->create();

        $response = $this->putJson("/api/property-types/{$propertyType->slug}", [
            'name' => 'Villa',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_update_property_type(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $propertyType = PropertyType::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/property-types/{$propertyType->slug}", [
            'name' => 'Villa',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_user_can_update_property_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $propertyType = PropertyType::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/property-types/{$propertyType->slug}", [
            'name' => 'Villa',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('property_types', [
            'id' => $propertyType->id,
            'name' => 'Villa',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_property_type(): void
    {
        $propertyType = PropertyType::factory()->create();

        $response = $this->deleteJson("/api/property-types/{$propertyType->slug}");

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_delete_property_type(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $propertyType = PropertyType::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/property-types/{$propertyType->slug}");

        $response->assertStatus(403);
    }

    public function test_admin_user_can_delete_property_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $propertyType = PropertyType::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/property-types/{$propertyType->slug}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('property_types', [
            'id' => $propertyType->id,
        ]);
    }
}
