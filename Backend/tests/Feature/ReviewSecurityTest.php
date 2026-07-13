<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_update_review()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Old content',
            'rate' => 5,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'New content',
            'rate' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5,
        ]);

        $this->actingAs($otherUser, 'sanctum');

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Attempted hijack',
            'rate' => 1,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Original content',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_review()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_others_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        $this->actingAs($otherUser, 'sanctum');

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'deleted_at' => null]);
    }

    public function test_guest_cannot_access_admin_dashboard()
    {
        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_reports()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);
    }
}
