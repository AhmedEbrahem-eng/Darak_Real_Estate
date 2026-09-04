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

    private function createPropertyWithOwner($owner)
    {
        $location = Location::create([
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Main St'
        ]);

        $propertyType = PropertyType::create([
            'name' => 'Apartment'
        ]);

        return Property::create([
            'title' => 'Sample Property',
            'description' => 'A nice property',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120,
            'price' => 5000,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'user_id' => $owner->id,
            'availability' => 'available',
            'listing_type' => 'rent'
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'uploads/test.jpg'
        ]);

        $response = $this->deleteJson('/api/images/' . $image->id);

        $response->assertStatus(401);
    }

    public function test_non_owner_cannot_delete_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'uploads/test.jpg'
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson('/api/images/' . $image->id);

        $response->assertStatus(403);
    }

    public function test_property_owner_can_delete_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'uploads/test.jpg'
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->deleteJson('/api/images/' . $image->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_admin_can_delete_any_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createPropertyWithOwner($owner);
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'uploads/test.jpg'
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/images/' . $image->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }
}
