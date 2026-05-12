<?php

namespace Tests\Feature;

use App\Models\Amenity;
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

    protected function setUp(): void
    {
        parent::setUp();
        // Seed necessary data for factories
        User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    public function test_unauthenticated_user_cannot_update_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated content',
            'rate' => 5,
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_others_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $attacker = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Attacker update',
            'rate' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_others_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $attacker = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_update_own_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated by owner',
            'rate' => 4,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Updated by owner',
        ]);
    }

    public function test_owner_can_delete_own_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'review deleted successfully']);
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
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'review deleted successfully']);
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }
}
