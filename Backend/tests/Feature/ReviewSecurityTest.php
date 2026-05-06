<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Review;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed prerequisites for PropertyFactory
        $this->user = User::factory()->create();
        $this->location = Location::factory()->create();
        $this->propertyType = PropertyType::factory()->create();

        // Create a property
        $this->property = Property::factory()->create([
            'user_id' => $this->user->id,
            'location_id' => $this->location->id,
            'property_type_id' => $this->propertyType->id,
        ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_update_or_delete_reviews()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Updated content',
            'rate' => 5
        ]);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/reviews/{$review->id}");
        $response->assertStatus(401);
    }

    /** @test */
    public function users_cannot_update_others_reviews_idor()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $attacker = User::factory()->create(['role' => 'user']);
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
            'content' => 'Original content'
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'content' => 'I am updating your review!',
            'rate' => 1
        ]);

        $response->assertStatus(404);
        $this->assertEquals('Original content', $review->refresh()->content);
    }

    /** @test */
    public function users_cannot_delete_others_reviews_idor()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $attacker = User::factory()->create(['role' => 'user']);
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function users_can_update_their_own_reviews()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
            'content' => 'Original content',
            'rate' => 3
        ]);

        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Updated content',
            'rate' => 5
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated content', $review->refresh()->content);
    }

    /** @test */
    public function admins_can_update_any_review()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
            'content' => 'Original content'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Admin update',
            'rate' => 5
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Admin update', $review->refresh()->content);
    }
}
