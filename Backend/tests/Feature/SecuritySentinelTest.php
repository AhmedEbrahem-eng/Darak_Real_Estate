<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary records for review
        $propertyType = PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment']);
        $location = Location::create([
            'city' => 'Maadi',
            'state' => 'Cairo',
            'street' => '9th Street'
        ]);

        $this->landlord = User::factory()->create(['role' => 'landlord']);
        $this->property = Property::create([
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'listing_type' => 'rent',
            'availability' => 'available',
            'status' => 'accepted',
            'user_id' => $this->landlord->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'slug' => 'test-property'
        ]);
    }

    public function test_user_cannot_update_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
            'content' => 'Original Content',
            'rate' => 5
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Hacked Content',
            'rate' => 1,
            'property_id' => $this->property->id
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Original Content'
        ]);
    }

    public function test_user_cannot_delete_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
            'content' => 'Delete Me',
            'rate' => 5
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'deleted_at' => null
        ]);
    }

    public function test_admin_can_delete_any_review()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
            'content' => 'Admin will delete this',
            'rate' => 5
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id
        ]);
    }

    public function test_owner_can_update_own_review()
    {
        $owner = User::factory()->create();
        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $this->property->id,
            'content' => 'My review',
            'rate' => 5
        ]);

        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Updated content',
            'rate' => 4,
            'property_id' => $this->property->id // Required by UpdateReviewRequest probably
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Updated content'
        ]);
    }
}
