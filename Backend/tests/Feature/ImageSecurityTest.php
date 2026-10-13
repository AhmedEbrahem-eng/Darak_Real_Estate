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

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic location and property type if required
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    public function test_unauthenticated_user_cannot_delete_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $image = PropertyImage::factory()->create(['property_id' => $property->id]);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(401);
    }

    public function test_non_owner_user_cannot_delete_property_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $image = PropertyImage::factory()->create(['property_id' => $property->id]);

        $response = $this->actingAs($otherUser)->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(403);
    }

    public function test_property_owner_can_delete_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $image = PropertyImage::factory()->create(['property_id' => $property->id]);

        $response = $this->actingAs($owner)->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_admin_can_delete_any_property_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $image = PropertyImage::factory()->create(['property_id' => $property->id]);

        $response = $this->actingAs($admin)->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_deleting_non_existent_image_returns_404()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->deleteJson("/api/images/99999");

        $response->assertStatus(404);
    }
}
