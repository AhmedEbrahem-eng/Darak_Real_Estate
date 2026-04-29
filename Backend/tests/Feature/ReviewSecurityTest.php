<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use App\Models\Review;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $property;

    protected function setUp(): void
    {
        parent::setUp();
        // Create necessary dependencies for Property
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create(['name' => 'Apartment', 'slug' => 'apartment']);
        $user = User::factory()->create();

        $this->property = Property::factory()->create([
            'user_id' => $user->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_review()
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Updated content',
            'rate' => 5,
            'property_id' => $this->property->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_review()
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Malicious update',
            'rate' => 1,
            'property_id' => $this->property->id,
        ]);

        // Expect 403 Forbidden
        $response->assertStatus(403);
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'content' => 'Malicious update'
        ]);
    }

    public function test_user_cannot_delete_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        // Expect 403 Forbidden
        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'deleted_at' => null
        ]);
    }

    public function test_owner_can_update_own_review()
    {
        $owner = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Updated by owner',
            'rate' => 4,
            'property_id' => $review->property_id
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Updated by owner'
        ]);
    }

    public function test_owner_can_delete_own_review()
    {
        $owner = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id
        ]);
    }
}
