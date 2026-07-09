<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Review;
use App\Models\User;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SentinelSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic data for tests
        $this->location = Location::factory()->create();
        $this->propertyType = PropertyType::factory()->create();
    }

    /** @test */
    public function a_guest_cannot_delete_an_image()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id, 'location_id' => $this->location->id, 'property_type_id' => $this->propertyType->id]);
        $image = PropertyImage::factory()->create(['property_id' => $property->id]);

        $response = $this->deleteJson("/api/images/{$image->id}");

        // Vulnerable: currently returns 200/delete if guest
        // Secure: should return 401
        $response->assertStatus(401);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_property_image()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id, 'location_id' => $this->location->id, 'property_type_id' => $this->propertyType->id]);
        $image = PropertyImage::factory()->create(['property_id' => $property->id]);

        $this->actingAs($attacker, 'sanctum');
        $response = $this->deleteJson("/api/images/{$image->id}");

        // Vulnerable: currently allows deletion
        // Secure: should return 403
        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_update_another_users_review()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id, 'location_id' => $this->location->id, 'property_type_id' => $this->propertyType->id]);
        $review = Review::factory()->create(['user_id' => $owner->id, 'property_id' => $property->id]);

        $this->actingAs($attacker, 'sanctum');
        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Hacked content',
            'rate' => 1
        ]);

        // Vulnerable: currently allows update
        // Secure: should return 403
        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_review()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id, 'location_id' => $this->location->id, 'property_type_id' => $this->propertyType->id]);
        $review = Review::factory()->create(['user_id' => $owner->id, 'property_id' => $property->id]);

        $this->actingAs($attacker, 'sanctum');
        $response = $this->deleteJson("/api/reviews/{$review->id}");

        // Vulnerable: currently allows deletion
        // Secure: should return 403
        $response->assertStatus(403);
    }
}
