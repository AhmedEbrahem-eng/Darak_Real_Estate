<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $attacker;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->attacker = User::factory()->create();

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
    }

    public function test_guest_cannot_update_review()
    {
        $review = Review::create([
            'user_id' => $this->owner->id,
            'property_id' => $this->property->id,
            'rate' => 5,
            'content' => 'Original Content',
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'rate' => 1,
            'content' => 'Updated by Guest',
        ]);

        // Expectation: Guests should be blocked.
        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_review()
    {
        $review = Review::create([
            'user_id' => $this->owner->id,
            'property_id' => $this->property->id,
            'rate' => 5,
            'content' => 'Original Content',
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        // Expectation: Guests should be blocked.
        $response->assertStatus(401);
    }

    public function test_user_cannot_update_another_users_review()
    {
        $review = Review::create([
            'user_id' => $this->owner->id,
            'property_id' => $this->property->id,
            'rate' => 5,
            'content' => 'Original Content',
        ]);

        Sanctum::actingAs($this->attacker);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'rate' => 1,
            'content' => 'Updated by Attacker',
        ]);

        // Expectation: 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_review()
    {
        $review = Review::create([
            'user_id' => $this->owner->id,
            'property_id' => $this->property->id,
            'rate' => 5,
            'content' => 'Original Content',
        ]);

        Sanctum::actingAs($this->attacker);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        // Expectation: 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_owner_can_update_own_review()
    {
        $review = Review::create([
            'user_id' => $this->owner->id,
            'property_id' => $this->property->id,
            'rate' => 5,
            'content' => 'Original Content',
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'rate' => 4,
            'content' => 'Updated by Owner',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Updated by Owner',
            'deleted_at' => null,
        ]);
    }

    public function test_owner_can_delete_own_review()
    {
        $review = Review::create([
            'user_id' => $this->owner->id,
            'property_id' => $this->property->id,
            'rate' => 5,
            'content' => 'Original Content',
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);

        // Assert it is soft deleted
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
