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
    protected $property;
    protected $image;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Users explicitly to prevent pluck errors on empty database
        $this->owner = User::factory()->create(['role' => 'landlord']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create Location and PropertyType
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        // Create a property belonging to $this->owner
        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        // Create a property image belonging to the property
        $this->image = PropertyImage::factory()->create([
            'property_id' => $this->property->id,
            'image' => 'images/properties/test_image.jpg',
        ]);
    }

    /**
     * Test that unauthenticated users cannot delete images.
     */
    public function test_unauthenticated_user_cannot_delete_image(): void
    {
        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(401);
    }

    /**
     * Test that users who do not own the property containing the image cannot delete it.
     */
    public function test_non_owner_cannot_delete_image(): void
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Unauthorized']);
    }

    /**
     * Test that the property owner can successfully delete the image.
     */
    public function test_owner_can_delete_image(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Image deleted successfully']);

        $this->assertSoftDeleted('property_images', [
            'id' => $this->image->id,
        ]);
    }

    /**
     * Test that an administrator can delete any image.
     */
    public function test_admin_can_delete_image(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/images/{$this->image->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Image deleted successfully']);

        $this->assertSoftDeleted('property_images', [
            'id' => $this->image->id,
        ]);
    }

    /**
     * Test that requesting to delete a non-existent image returns 404.
     */
    public function test_delete_non_existent_image_returns_404(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/images/9999");

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Image dosen`t exist']);
    }
}
