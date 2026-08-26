<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createPropertyForUser(User $user)
    {
        $location = Location::create([
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Main St',
        ]);

        $propertyType = PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment',
        ]);

        return Property::create([
            'title' => 'Sample Property',
            'slug' => 'sample-property-' . rand(100, 999),
            'description' => 'A nice place',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available',
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'status' => 'accepted',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(401);
    }

    public function test_non_owner_user_cannot_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->actingAs($otherUser)->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized to delete this property']);
    }

    public function test_property_owner_can_delete_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->actingAs($owner)->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Property deleted successfully']);

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_delete_any_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createPropertyForUser($owner);

        $response = $this->actingAs($admin)->deleteJson('/api/properties/' . $property->id);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Property deleted successfully']);

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_delete_non_existent_property_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->deleteJson('/api/properties/99999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Property not found']);
    }
}
