<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createPropertyForUser($user)
    {
        $location = Location::create([
            'city' => 'Test City',
            'state' => 'Test State',
            'street' => 'Test Street',
        ]);

        $propertyType = PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment',
        ]);

        return Property::create([
            'title' => 'Test Property',
            'slug' => 'test-property-' . uniqid(),
            'description' => 'A test property description',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'listing_type' => 'rent',
            'availability' => 'available',
            'status' => 'accepted',
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_image()
    {
        $owner = User::factory()->create();
        $property = $this->createPropertyForUser($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(401);
    }

    public function test_non_owner_cannot_delete_image()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(403);
    }

    public function test_property_owner_can_delete_image()
    {
        $owner = User::factory()->create();
        $property = $this->createPropertyForUser($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Image deleted successfully']);

        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_admin_can_delete_any_image()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createPropertyForUser($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'images/test.jpg',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Image deleted successfully']);

        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_returns_404_if_image_does_not_exist()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/images/99999');

        $response->assertStatus(404);
    }
}
