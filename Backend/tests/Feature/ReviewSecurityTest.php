<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createProperty(): Property
    {
        $user = User::factory()->create();
        $location = Location::create([
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Main St',
        ]);
        $type = PropertyType::create(['name' => 'Apartment']);

        return Property::create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $type->id,
            'title' => 'Sample Apartment',
            'description' => 'A nice apartment',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'listing_type' => 'rent',
            'availability' => 'available',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_or_delete_review()
    {
        $property = $this->createProperty();
        $owner = User::factory()->create();
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great place',
            'rate' => 5,
        ]);

        $updateResponse = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated content',
            'rate' => 4,
        ]);
        $updateResponse->assertStatus(401);

        $deleteResponse = $this->deleteJson("/api/reviews/{$review->id}");
        $deleteResponse->assertStatus(401);
    }

    public function test_user_cannot_update_another_users_review()
    {
        $property = $this->createProperty();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great place',
            'rate' => 5,
        ]);

        $token = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/reviews/{$review->id}", [
                'property_id' => $property->id,
                'content' => 'Hacked content',
                'rate' => 1,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_review()
    {
        $property = $this->createProperty();
        $owner = User::factory()->create();
        $otherUser = User::factory()->create(['role' => 'user']);
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great place',
            'rate' => 5,
        ]);

        $token = $otherUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_review_owner_can_update_and_delete_own_review()
    {
        $property = $this->createProperty();
        $owner = User::factory()->create(['role' => 'user']);
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great place',
            'rate' => 5,
        ]);

        $token = $owner->createToken('test-token')->plainTextToken;

        $updateResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/reviews/{$review->id}", [
                'property_id' => $property->id,
                'content' => 'Updated review content',
                'rate' => 4,
            ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Updated review content',
            'rate' => 4,
        ]);

        $deleteResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/reviews/{$review->id}");

        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_admin_can_delete_any_review()
    {
        $property = $this->createProperty();
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great place',
            'rate' => 5,
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_returns_404_for_non_existent_review()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/reviews/99999');

        $response->assertStatus(404);
    }
}
