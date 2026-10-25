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

    public function test_unauthenticated_user_cannot_update_or_delete_review()
    {
        $owner = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Great place',
            'rate' => 5,
        ]);

        $updateResponse = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'rate' => 5,
            'content' => 'Updated content',
        ]);
        $updateResponse->assertStatus(401);

        $deleteResponse = $this->deleteJson("/api/reviews/{$review->id}");
        $deleteResponse->assertStatus(401);
    }

    public function test_non_owner_cannot_update_or_delete_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Original review',
            'rate' => 4,
        ]);

        $updateResponse = $this->actingAs($otherUser, 'sanctum')
            ->putJson("/api/reviews/{$review->id}", [
                'property_id' => $property->id,
                'rate' => 4,
                'content' => 'Unauthorized update',
            ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}");
        $deleteResponse->assertStatus(403);
    }

    public function test_owner_can_update_and_delete_their_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 3,
        ]);

        $updateResponse = $this->actingAs($owner, 'sanctum')
            ->putJson("/api/reviews/{$review->id}", [
                'property_id' => $property->id,
                'rate' => 5,
                'content' => 'Updated review content',
            ]);
        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Updated review content',
        ]);

        $deleteResponse = $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}");
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_admin_can_delete_any_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Review to be deleted by admin',
            'rate' => 2,
        ]);

        $deleteResponse = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}");
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_update_or_delete_non_existent_review_returns_404()
    {
        $user = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();

        $updateResponse = $this->actingAs($user, 'sanctum')
            ->putJson("/api/reviews/99999", [
                'property_id' => $property->id,
                'rate' => 5,
                'content' => 'Non existent update',
            ]);
        $updateResponse->assertStatus(404);

        $deleteResponse = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/reviews/99999");
        $deleteResponse->assertStatus(404);
    }
}
