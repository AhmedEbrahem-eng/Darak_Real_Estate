<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use App\Models\Property;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic data
        $this->location = Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $this->propertyType = PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment']);
    }

    public function test_guest_cannot_access_reports()
    {
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->getJson('/api/report-properties')->assertStatus(401);
    }

    public function test_regular_user_cannot_access_reports()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/report-users')->assertStatus(403);
        $this->getJson('/api/report-properties')->assertStatus(403);
    }

    public function test_guest_cannot_access_dashboard()
    {
        $this->getJson('/api/dashboard/home')->assertStatus(401);
    }

    public function test_regular_user_cannot_access_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/home')->assertStatus(403);
    }

    public function test_user_cannot_update_others_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $property = Property::create([
            'title' => 'Test Property',
            'slug' => 'test-property',
            'description' => 'Description',
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'area' => 100,
            'price' => 1000,
            'location_id' => $this->location->id,
            'property_type_id' => $this->propertyType->id,
            'user_id' => $user1->id,
            'availability' => 'available',
            'listing_type' => 'rent',
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Original Review',
            'rate' => 5,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated Review',
            'rate' => 1,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Original Review',
        ]);
    }

    public function test_user_cannot_delete_others_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $property = Property::create([
            'title' => 'Test Property',
            'slug' => 'test-property',
            'description' => 'Description',
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'area' => 100,
            'price' => 1000,
            'location_id' => $this->location->id,
            'property_type_id' => $this->propertyType->id,
            'user_id' => $user1->id,
            'availability' => 'available',
            'listing_type' => 'rent',
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Review to delete',
            'rate' => 5,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'deleted_at' => null,
        ]);
    }
}
