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

    public function test_unauthenticated_user_cannot_delete_image(): void
    {
        $response = $this->deleteJson('/api/images/1');
        $response->assertStatus(401);
    }

    public function test_non_existent_image_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/images/999999');
        $response->assertStatus(404);
    }

    public function test_unauthorized_user_cannot_delete_another_users_property_image(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $image = PropertyImage::factory()->create([
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/images/{$image->id}");
        $response->assertStatus(403);
    }

    public function test_property_owner_can_delete_image(): void
    {
        $owner = User::factory()->create();

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $image = PropertyImage::factory()->create([
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/images/{$image->id}");
        $response->assertStatus(200);
    }

    public function test_admin_user_can_delete_image(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $image = PropertyImage::factory()->create([
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/images/{$image->id}");
        $response->assertStatus(200);
    }
}
