<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_own_review()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated comment',
            'rate' => 4
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated comment', $review->fresh()->content);
    }

    public function test_user_cannot_update_others_review()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Hacked comment',
            'rate' => 1
        ]);

        $response->assertStatus(403);
    }
}
