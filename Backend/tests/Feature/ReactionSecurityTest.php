<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Reaction;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReactionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function createReactionForUser(User $user): Reaction
    {
        $propertyType = PropertyType::create(['name' => 'Apartment']);
        $location = Location::create([
            'city' => 'Sample City',
            'state' => 'Sample State',
            'street' => '123 Main St',
        ]);

        $property = Property::create([
            'user_id' => $user->id,
            'title' => 'Sample Property',
            'description' => 'A nice sample property',
            'price' => 1000,
            'area' => 120,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'listing_type' => 'rent',
            'availability' => 'available',
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'rate' => 5,
            'content' => 'Great property!',
        ]);

        return Reaction::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
            'type' => 'like',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_reaction(): void
    {
        $owner = User::factory()->create();
        $reaction = $this->createReactionForUser($owner);

        $response = $this->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('reactions', ['id' => $reaction->id]);
    }

    public function test_user_cannot_delete_another_users_reaction(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $reaction = $this->createReactionForUser($owner);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reactions', ['id' => $reaction->id]);
    }

    public function test_owner_can_delete_own_reaction(): void
    {
        $owner = User::factory()->create();
        $reaction = $this->createReactionForUser($owner);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reactions', ['id' => $reaction->id]);
    }

    public function test_admin_can_delete_any_reaction(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $reaction = $this->createReactionForUser($owner);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reactions/{$reaction->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('reactions', ['id' => $reaction->id]);
    }
}
