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

    /** @test */
    public function unauthenticated_users_cannot_access_administrative_endpoints()
    {
        $endpoints = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function non_admin_users_cannot_access_administrative_endpoints()
    {
        $user = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($user);

        $endpoints = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(403);
        }
    }

    /** @test */
    public function users_cannot_delete_other_users_reviews()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $location = Location::create(['city' => 'Test', 'state' => 'Test', 'street' => 'Test']);
        $type = PropertyType::create(['name' => 'Test']);

        $property = Property::create([
            'title' => 'Test Property',
            'description' => 'Test',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available',
            'property_type_id' => $type->id,
            'location_id' => $location->id,
            'user_id' => $user1->id,
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'rate' => 5,
            'content' => 'Great!',
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        // This is expected to FAIL before the fix
        $response->assertStatus(403);
    }
}
