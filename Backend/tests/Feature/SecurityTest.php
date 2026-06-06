<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use App\Models\Review;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    /** @test */
    public function unauthenticated_users_cannot_access_admin_dashboard()
    {
        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(401);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(403);
    }

    /** @test */
    public function users_cannot_delete_properties_they_do_not_own()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        // This is expected to fail initially as the current implementation likely returns 200/400 but allows the action
        // or doesn't check ownership.
        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_users_cannot_approve_tours()
    {
        $landlord = User::factory()->create();
        $renter = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create(['user_id' => $renter->id, 'property_id' => $property->id]);
        $tourDate = TourDate::create(['tour_id' => $tour->id, 'date' => now()->addDay()]);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id]);

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthorized_users_cannot_approve_tours()
    {
        $landlord = User::factory()->create();
        $otherUser = User::factory()->create();
        $renter = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create(['user_id' => $renter->id, 'property_id' => $property->id]);
        $tourDate = TourDate::create(['tour_id' => $tour->id, 'date' => now()->addDay()]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id]);

        $response->assertStatus(403);
    }

    /** @test */
    public function users_cannot_update_reviews_they_did_not_write()
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'property_id' => $property->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated content',
            'rate' => 5
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function users_cannot_delete_reviews_they_did_not_write()
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'property_id' => $property->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }
}
