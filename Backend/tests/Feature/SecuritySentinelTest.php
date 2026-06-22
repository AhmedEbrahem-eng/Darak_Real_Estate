<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use App\Models\Property;
use App\Models\Amenity;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_access_admin_routes()
    {
        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['DELETE', '/api/report-users/deleteReport/1'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/reason-report'],
            ['POST', '/api/amenities'],
            ['POST', '/api/property-types'],
            ['GET', '/api/dashboard/home'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function regular_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $adminRoutes = [
            ['GET', '/api/report-users'],
            ['GET', '/api/report-properties'],
            ['GET', '/api/reason-report'],
            ['POST', '/api/amenities'],
            ['POST', '/api/property-types'],
            ['GET', '/api/dashboard/home'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(403);
        }
    }

    /** @test */
    public function users_cannot_delete_others_reviews()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $location = \App\Models\Location::factory()->create();
        $type = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $user1->id,
            'location_id' => $location->id,
            'property_type_id' => $type->id
        ]);
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'property_id' => $property->id
        ]);

        Sanctum::actingAs($user2);
        $response = $this->deleteJson("/api/reviews/{$review->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function users_can_delete_their_own_reviews()
    {
        $user = User::factory()->create();
        $location = \App\Models\Location::factory()->create();
        $type = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $type->id
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id
        ]);

        Sanctum::actingAs($user);
        $response = $this->deleteJson("/api/reviews/{$review->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function admins_can_delete_any_review()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $location = \App\Models\Location::factory()->create();
        $type = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $type->id
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id
        ]);

        Sanctum::actingAs($admin);
        $response = $this->deleteJson("/api/reviews/{$review->id}");
        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
