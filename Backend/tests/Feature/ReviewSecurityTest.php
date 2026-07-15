<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cannot_update_reviews()
    {
        $review = Review::factory()->create();

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $review->property_id,
            'content' => 'Updated content',
            'rate' => 5
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthenticated_users_cannot_delete_reviews()
    {
        $review = Review::factory()->create();

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function users_cannot_update_other_users_reviews()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $review->property_id,
            'content' => 'Updated content',
            'rate' => 5
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function users_cannot_delete_other_users_reviews()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function users_can_update_their_own_reviews()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $review->property_id,
            'content' => 'Updated content',
            'rate' => 5
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Updated content'
        ]);
    }

    /** @test */
    public function users_can_delete_their_own_reviews()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function admins_can_delete_any_review()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
