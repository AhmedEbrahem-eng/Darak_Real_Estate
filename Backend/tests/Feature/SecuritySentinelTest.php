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

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary data for factories that use pluck('id')->random()
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    /** @test */
    public function guests_cannot_access_administrative_endpoints()
    {
        $endpoints = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/dashboard/home'],
            ['POST', '/api/amenities'],
            ['POST', '/api/reason-report'],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            // Sanctum returns 401 for unauthenticated requests
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function regular_users_cannot_access_administrative_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);
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
    public function users_cannot_delete_others_properties()
    {
        $owner = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'landlord']);

        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function users_cannot_update_others_reviews()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Hacked content',
            'rate' => 1
        ]);

        $response->assertStatus(403);
    }
}
