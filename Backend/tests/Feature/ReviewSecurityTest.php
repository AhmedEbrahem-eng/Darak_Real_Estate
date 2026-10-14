<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $otherUser;
    protected $admin;
    protected $property;
    protected $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $this->review = Review::create([
            'user_id' => $this->owner->id,
            'property_id' => $this->property->id,
            'content' => 'Great property!',
            'rate' => 5,
        ]);
    }

    public function test_unauthenticated_user_cannot_update_review(): void
    {
        $response = $this->putJson("/api/reviews/{$this->review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Hacked content',
            'rate' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_review(): void
    {
        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(401);
    }

    public function test_non_owner_cannot_update_review(): void
    {
        $token = $this->otherUser->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/reviews/{$this->review->id}", [
                'property_id' => $this->property->id,
                'content' => 'Attempted unauthorized update',
                'rate' => 2,
            ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_cannot_delete_review(): void
    {
        $token = $this->otherUser->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_update_own_review(): void
    {
        $token = $this->owner->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/reviews/{$this->review->id}", [
                'property_id' => $this->property->id,
                'content' => 'Updated review content',
                'rate' => 4,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'content' => 'Updated review content',
            'rate' => 4,
        ]);
    }

    public function test_owner_can_delete_own_review(): void
    {
        $token = $this->owner->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $this->review->id]);
    }

    public function test_admin_can_delete_any_review(): void
    {
        $token = $this->admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $this->review->id]);
    }

    public function test_updating_non_existent_review_returns_404(): void
    {
        $token = $this->owner->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/reviews/999999", [
                'property_id' => $this->property->id,
                'content' => 'Non existent',
                'rate' => 3,
            ]);

        $response->assertStatus(404);
    }
}
