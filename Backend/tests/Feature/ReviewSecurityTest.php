<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherUser;
    protected $admin;
    protected $property;
    protected $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'renter']);
        $this->otherUser = User::factory()->create(['role' => 'renter']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $location = Location::create([
            'city' => 'Test City',
            'state' => 'Test State',
            'street' => 'Test Street'
        ]);

        $propertyType = PropertyType::create(['name' => 'Apartment']);

        $this->property = Property::create([
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 1000,
            'listing_type' => 'rent',
            'status' => 'accepted',
            'availability' => 'available',
            'user_id' => $this->admin->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'area' => 100,
            'slug' => 'test-property'
        ]);

        $this->review = Review::create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'content' => 'Original Content',
            'rate' => 5,
        ]);
    }

    /** @test */
    public function guest_cannot_update_review()
    {
        $response = $this->putJson("/api/reviews/{$this->review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Updated Content',
            'rate' => 4
        ]);

        // Current behavior might be 200/404/500 depending on implementation,
        // but it SHOULD be 401 Unauthorized
        $response->assertStatus(401);
    }

    /** @test */
    public function guest_cannot_delete_review()
    {
        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        // Current behavior might be 200 because it's public,
        // but it SHOULD be 401 Unauthorized
        $response->assertStatus(401);
    }

    /** @test */
    public function user_cannot_update_others_review()
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->putJson("/api/reviews/{$this->review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Updated Content',
            'rate' => 4
        ]);

        // SHOULD be 403 Forbidden
        $response->assertStatus(403);
    }

    /** @test */
    public function user_cannot_delete_others_review()
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        // SHOULD be 403 Forbidden
        $response->assertStatus(403);
    }

    /** @test */
    public function owner_can_update_own_review()
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/reviews/{$this->review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Updated Content',
            'rate' => 4
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $this->review->id,
            'content' => 'Updated Content'
        ]);
    }

    /** @test */
    public function owner_can_delete_own_review()
    {
        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $this->review->id]);
    }

    /** @test */
    public function admin_can_delete_any_review()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/reviews/{$this->review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $this->review->id]);
    }
}
