<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
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

        // Ensure we have some default data for factories that use pluck()->random()
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    public function test_unauthenticated_user_cannot_update_review()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated content',
            'rate' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated content',
            'rate' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_delete_review()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }
}
