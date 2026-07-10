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

        // Setup necessary data for reviews
        $this->location = Location::create([
            'city' => 'Test City',
            'state' => 'Test State',
            'street' => 'Test Street'
        ]);

        $this->propertyType = PropertyType::create([
            'name' => 'Apartment'
        ]);

        $this->landlord = User::create([
            'first_name' => 'Landlord',
            'last_name' => 'User',
            'email' => 'landlord@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'phone_number' => 123456789,
            'avatar' => 'avatar.png'
        ]);

        $this->property = Property::create([
            'user_id' => $this->landlord->id,
            'location_id' => $this->location->id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 1000,
            'property_type_id' => $this->propertyType->id,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available',
            'slug' => 'test-property',
            'area' => 100
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $this->property->id,
            'content' => 'User 1 review',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'deleted_at' => null]);
    }

    /** @test */
    public function a_user_cannot_update_another_users_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $this->property->id,
            'content' => 'User 1 review',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $this->property->id,
            'content' => 'Updated by User 2',
            'rate' => 1
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'User 1 review'
        ]);
    }

    /** @test */
    public function an_admin_can_delete_any_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $this->property->id,
            'content' => 'User review',
            'rate' => 5
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function a_guest_cannot_access_admin_endpoints()
    {
        $endpoints = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/reason-report'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function a_regular_user_cannot_access_admin_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $endpoints = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/reason-report'],
            ['GET', '/api/dashboard/home'],
            ['GET', '/api/amenities'],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(403);
        }
    }
}
