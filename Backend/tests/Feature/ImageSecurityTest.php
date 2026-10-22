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
        ]);

        return Property::create([
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'listing_type' => 'rent',
            'availability' => 'available',
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_image(): void
    {
        $response = $this->deleteJson('/api/images/1');
        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_image_of_another_users_property(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'test.jpg',
        ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(403);
    }

    public function test_non_existent_image_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->deleteJson('/api/images/999999');

        $response->assertStatus(404);
    }

    public function test_property_owner_can_delete_image(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'test.jpg',
        ]);

        $response = $this->actingAs($owner)
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_admin_can_delete_any_image(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createPropertyForUser($owner);

        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'test.jpg',
        ]);

        $response = $this->actingAs($admin)
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }
}
