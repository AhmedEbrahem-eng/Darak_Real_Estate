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

    private $owner;
    private $nonOwner;
    private $admin;
    private $property;
    private $image;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard location and property type
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        // Create users
        $this->owner = User::factory()->create(['role' => 'user']);
        $this->nonOwner = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create property owned by the owner
        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        // Create image for the property
        $this->image = PropertyImage::factory()->create([
            'property_id' => $this->property->id,
        ]);
    }

    public function test_unauthenticated_request_cannot_delete_image(): void
    {
        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(401);
    }

    public function test_non_owner_and_non_admin_cannot_delete_image(): void
    {
        Sanctum::actingAs($this->nonOwner);

        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'You are not authorized to delete this image']);
    }

    public function test_property_owner_can_delete_image(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Image deleted successfully']);
    }

    public function test_admin_can_delete_image(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Image deleted successfully']);
    }

    public function test_delete_non_existent_image_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/images/99999");

        $response->assertStatus(404);
        $response->assertJsonFragment(['message' => 'Image dosen`t exist']);
    }
}
