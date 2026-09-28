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

    protected $owner;
    protected $otherUser;
    protected $admin;
    protected $propertyImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $location = Location::create([
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Main St',
        ]);

        $propertyType = PropertyType::create([
            'name' => 'Apartment',
        ]);

        $property = Property::create([
            'title' => 'Test Apartment',
            'slug' => 'test-apartment',
            'description' => 'A nice place',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120,
            'price' => 500000,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'user_id' => $this->owner->id,
            'availability' => 'available',
            'listing_type' => 'rent',
            'status' => 'accepted',
        ]);

        $this->propertyImage = PropertyImage::create([
            'property_id' => $property->id,
            'image' => 'uploads/properties/test.jpg',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_image(): void
    {
        $response = $this->deleteJson("/api/images/{$this->propertyImage->id}");

        $response->assertStatus(401);
    }

    public function test_non_owner_cannot_delete_image(): void
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->deleteJson("/api/images/{$this->propertyImage->id}");

        $response->assertStatus(403);
    }

    public function test_property_owner_can_delete_image(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/images/{$this->propertyImage->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $this->propertyImage->id]);
    }

    public function test_admin_can_delete_any_image(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/images/{$this->propertyImage->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $this->propertyImage->id]);
    }
}
