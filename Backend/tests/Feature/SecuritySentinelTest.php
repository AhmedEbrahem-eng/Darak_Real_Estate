<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_delete_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $propertyType = PropertyType::create(['name' => 'House', 'slug' => 'house']);
        $location = Location::create(['city' => 'Test', 'state' => 'Test', 'street' => 'Test']);

        $property = Property::create([
            'title' => 'Test Property',
            'slug' => 'test-property',
            'description' => 'Test Description',
            'price' => 1000,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 100,
            'listing_type' => 'rent',
            'availability' => 'available',
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'status' => 'accepted'
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Nice property',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'deleted_at' => null]);
    }

    public function test_guest_cannot_access_report_users_index()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_access_report_users_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/report-users');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_report_users_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/report-users');
        // If there are no reports, it returns 400 with "No reports found" based on controller
        $response->assertStatus(400);
    }

    public function test_unauthorized_user_cannot_update_property()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $propertyType = PropertyType::create(['name' => 'House Update', 'slug' => 'house-update']);
        $location = Location::create(['city' => 'Test', 'state' => 'Test', 'street' => 'Test']);

        $property = Property::create([
            'title' => 'Test Property',
            'slug' => 'test-property-update',
            'description' => 'Test Description',
            'price' => 1000,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 100,
            'listing_type' => 'rent',
            'availability' => 'available',
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'status' => 'accepted'
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/properties/{$property->slug}", [
            'title' => 'Updated Title'
        ]);

        $response->assertStatus(403);
    }
}
