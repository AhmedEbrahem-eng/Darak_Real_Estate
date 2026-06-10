<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_tour_requires_authentication()
    {
        $response = $this->postJson('/api/tours/1/approve', ['tour_date' => 1]);

        // Currently this might return 422 because it's not protected and validation fails
        // But it SHOULD return 401
        $response->assertStatus(401);
    }

    public function test_decline_tour_requires_authentication()
    {
        $response = $this->postJson('/api/tours/1/decline', ['message' => 'Sorry']);

        $response->assertStatus(401);
    }

    public function test_only_landlord_can_approve_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create(['property_id' => $property->id]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_routes_require_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            ['get', '/api/dashboard/home', null],
            ['get', '/api/amenities', null],
            ['post', '/api/amenities', []],
            ['get', '/api/report-users', null],
            ['get', '/api/report-properties', null],
        ];

        foreach ($adminRoutes as [$method, $url, $data]) {
            $response = $this->json($method, $url, $data ?? []);
            $response->assertStatus(403);
        }
    }
}
