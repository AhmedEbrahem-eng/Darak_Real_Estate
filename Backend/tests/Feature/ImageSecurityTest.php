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

    private function createPropertyWithImage(User $owner)
    {
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        return PropertyImage::factory()->create([
            'property_id' => $property->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_image()
    {
        $owner = User::factory()->create();
        $image = $this->createPropertyWithImage($owner);

        $response = $this->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(401);
    }

    public function test_non_owner_user_cannot_delete_property_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $image = $this->createPropertyWithImage($owner);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(403);
    }

    public function test_property_owner_can_delete_property_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $image = $this->createPropertyWithImage($owner);

        $response = $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_admin_can_delete_any_property_image()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $image = $this->createPropertyWithImage($owner);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('property_images', ['id' => $image->id]);
    }

    public function test_deleting_non_existent_image_returns_404()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/images/99999");

        $response->assertStatus(404);
    }
}
