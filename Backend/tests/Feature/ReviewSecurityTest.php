<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_delete_other_users_review()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'property_id' => $property->id
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_user_cannot_update_other_users_review()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'property_id' => $property->id
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Hacked Review',
            'rate' => 1,
            'property_id' => $property->id
        ]);

        $response->assertStatus(403);
    }
}
