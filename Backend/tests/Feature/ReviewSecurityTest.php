<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_update_or_delete_review(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Great property',
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

    public function test_user_cannot_update_other_users_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great property',
            'rate' => 5,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->putJson("/api/reviews/{$review->id}", [
                'property_id' => $property->id,
                'content' => 'Unauthorized edit',
                'rate' => 1,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_other_users_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great property',
            'rate' => 5,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_review(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great property',
            'rate' => 5,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_owner_can_update_and_delete_their_review(): void
    {
        $owner = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great property',
            'rate' => 5,
        ]);

        $updateResponse = $this->actingAs($owner, 'sanctum')
            ->putJson("/api/reviews/{$review->id}", [
                'property_id' => $property->id,
                'content' => 'Updated content',
                'rate' => 4,
            ]);
        $updateResponse->assertStatus(200);

        $deleteResponse = $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}");
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_returns_404_when_review_not_found(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();

        $updateResponse = $this->actingAs($user, 'sanctum')
            ->putJson("/api/reviews/99999", [
                'property_id' => $property->id,
                'content' => 'Updated content',
                'rate' => 4,
            ]);
        $updateResponse->assertStatus(404);

        $deleteResponse = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/reviews/99999");
        $deleteResponse->assertStatus(404);
    }
}
