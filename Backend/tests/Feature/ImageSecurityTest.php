<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ImageSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_delete_image()
    {
        $user = User::factory()->create();
        $location = Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = PropertyType::create(['name' => 'Test Type']);

        $property = Property::create([
            'title' => 'Test Property',
            'description' => 'Test Description',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120,
            'price' => 100000,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'user_id' => $user->id,
            'availability' => 'available',
            'listing_type' => 'buy',
            'status' => 'accepted'
        ]);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'test_image.jpg'
        ]);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(401);
        $this->assertNotSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_unauthorized_user_cannot_delete_other_users_image()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $location = Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = PropertyType::create(['name' => 'Test Type']);

        $property = Property::create([
            'title' => 'Owner Property',
            'description' => 'Test Description',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120,
            'price' => 100000,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'user_id' => $owner->id,
            'availability' => 'available',
            'listing_type' => 'buy',
            'status' => 'accepted'
        ]);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'owner_image.jpg'
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(403);
        $this->assertNotSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_owner_can_delete_own_image()
    {
        $owner = User::factory()->create();
        $location = Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = PropertyType::create(['name' => 'Test Type']);

        $property = Property::create([
            'title' => 'Owner Property',
            'description' => 'Test Description',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120,
            'price' => 100000,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'user_id' => $owner->id,
            'availability' => 'available',
            'listing_type' => 'buy',
            'status' => 'accepted'
        ]);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'owner_image.jpg'
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_admin_can_delete_any_image()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = PropertyType::create(['name' => 'Test Type']);

        $property = Property::create([
            'title' => 'Owner Property',
            'description' => 'Test Description',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120,
            'price' => 100000,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'user_id' => $owner->id,
            'availability' => 'available',
            'listing_type' => 'buy',
            'status' => 'accepted'
        ]);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'owner_image.jpg'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }
}
