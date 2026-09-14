<?php

namespace Tests\Feature;

use App\Models\Reaction;
use App\Models\User;
use App\Models\Review;
use App\Models\Property;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createReview(): Review
    {
        $location = Location::create([
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Main St',
        ]);

        $propertyType = PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment',
        ]);

        $owner = User::factory()->create(['role' => 'user']);

        $property = Property::create([
            'user_id' => $owner->id,
            'title' => 'Sample Property',
            'slug' => 'sample-property',
            'price' => 1000,
            'description' => 'A test property',
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'area' => 120,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available',
        ]);

        return Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great place!',
            'rate' => 5,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_reaction()
    {
        $response = $this->deleteJson('/api/reactions/1');

        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_another_users_reaction()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $review = $this->createReview();

        $reaction = Reaction::create([
            'user_id' => $user1->id,
            'review_id' => $review->id,
            'type' => 'like',
        ]);

        $response = $this->actingAs($user2, 'sanctum')->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_reaction()
    {
        $user = User::factory()->create(['role' => 'user']);
        $review = $this->createReview();

        $reaction = Reaction::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
            'type' => 'like',
        ]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reactions', ['id' => $reaction->id]);
    }

    public function test_admin_can_delete_any_users_reaction()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $review = $this->createReview();

        $reaction = Reaction::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
            'type' => 'like',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reactions', ['id' => $reaction->id]);
    }
}
