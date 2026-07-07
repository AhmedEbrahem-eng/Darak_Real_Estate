<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_delete_review()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $propertyType = PropertyType::create(['name' => 'House']);
        $location = Location::create([
            'city' => 'New York',
            'state' => 'NY',
            'street' => '5th Ave'
        ]);

        $property = Property::create([
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'title' => 'Nice House',
            'description' => 'A very nice house',
            'price' => 100000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'buy',
            'availability' => 'available'
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Great!',
            'rate' => 5
        ]);

        // Attempt to delete review as user2
        Sanctum::actingAs($user2);
        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_guest_cannot_delete_review()
    {
        $user1 = User::factory()->create();

        $propertyType = PropertyType::create(['name' => 'House']);
        $location = Location::create([
            'city' => 'New York',
            'state' => 'NY',
            'street' => '5th Ave'
        ]);

        $property = Property::create([
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'title' => 'Nice House',
            'description' => 'A very nice house',
            'price' => 100000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'buy',
            'availability' => 'available'
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Great!',
            'rate' => 5
        ]);

        // Attempt to delete review as guest
        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_unauthorized_user_cannot_update_review()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $propertyType = PropertyType::create(['name' => 'House']);
        $location = Location::create([
            'city' => 'New York',
            'state' => 'NY',
            'street' => '5th Ave'
        ]);

        $property = Property::create([
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'title' => 'Nice House',
            'description' => 'A very nice house',
            'price' => 100000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'buy',
            'availability' => 'available'
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Great!',
            'rate' => 5
        ]);

        // Attempt to update review as user2
        Sanctum::actingAs($user2);
        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Bad!',
            'rate' => 1
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Great!'
        ]);
    }
}
