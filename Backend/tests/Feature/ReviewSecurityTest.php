<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed necessary data if needed, but factories should handle it.
    }

    public function test_unauthenticated_user_cannot_update_review()
    {
        $user = User::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $propertyType = \App\Models\PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Updated content',
            'rate' => 5,
            'property_id' => $property->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_review()
    {
        $user = User::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $propertyType = \App\Models\PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_others_review()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $propertyType = \App\Models\PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user1->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Malicious update',
            'rate' => 1,
            'property_id' => $property->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_others_review()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $propertyType = \App\Models\PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user1->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_review()
    {
        $user = User::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $propertyType = \App\Models\PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Valid update',
            'rate' => 4,
            'property_id' => $review->property_id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Valid update',
        ]);
    }

    public function test_user_can_delete_own_review()
    {
        $user = User::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $propertyType = \App\Models\PropertyType::factory()->create();
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
