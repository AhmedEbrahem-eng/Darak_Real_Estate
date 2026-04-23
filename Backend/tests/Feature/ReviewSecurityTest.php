<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherUser;
    protected $property;
    protected $review;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Location and PropertyType since they are required by PropertyFactory
        Location::factory()->create();
        PropertyType::factory()->create();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $this->property = Property::factory()->create(['user_id' => $this->user->id]);

        $this->review = Review::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'content' => 'Original Content',
            'rate' => 5
        ]);
    }

    public function test_unauthorized_user_cannot_update_review()
    {
        $response = $this->putJson("/api/reviews/{$this->review->id}", [
            'content' => 'Updated Content',
            'rate' => 4,
            'property_id' => $this->property->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_delete_review()
    {
        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_others_review()
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->putJson("/api/reviews/{$this->review->id}", [
            'content' => 'Illegal Update',
            'rate' => 1,
            'property_id' => $this->property->id
        ]);

        // Expecting 403 Forbidden or 404 Not Found depending on implementation
        // Current implementation returns 404 if not found or failed to update
        // We will aim for 403 in our fix.
        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'content' => 'Original Content'
        ]);
    }

    public function test_user_cannot_delete_others_review()
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'deleted_at' => null
        ]);
    }

    public function test_user_can_update_own_review()
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/reviews/{$this->review->id}", [
            'content' => 'My Updated Content',
            'rate' => 4,
            'property_id' => $this->property->id
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'content' => 'My Updated Content'
        ]);
    }

    public function test_user_can_delete_own_review()
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $this->review->id]);
    }
}
