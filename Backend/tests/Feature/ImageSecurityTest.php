<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImageSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createPropertyWithOwner(User $owner)
    {
        $location = Location::create([
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Test Street',
        ]);

        $propertyType = PropertyType::create([
            'name' => 'Apartment',
        ]);

        return Property::create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 1000,
            'area' => 120,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available',
            'slug' => 'test-property',
            'status' => 'accepted',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_image()
    {
        $owner = User::factory()->create();
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(401);
    }

    public function test_non_owner_user_cannot_delete_image()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(403);
    }

    public function test_property_owner_can_delete_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Image deleted successfully']);
    }

    public function test_admin_user_can_delete_any_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Image deleted successfully']);
    }
}
