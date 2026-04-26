<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\Review;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_dashboard_overview()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_overview()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/dashboard/home');

        $response->assertStatus(200);
    }

    private function createProperty()
    {
        $user = User::factory()->create();
        $location = \App\Models\Location::create([
            'city' => 'Test City',
            'state' => 'Test State',
            'street' => 'Test Street'
        ]);
        $type = \App\Models\PropertyType::create([
            'name' => 'Test Type',
            'slug' => 'test-type-' . uniqid()
        ]);

        return Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $type->id,
        ]);
    }

    public function test_user_cannot_update_others_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $property = $this->createProperty();
        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Original review',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated review',
            'rate' => 1
        ]);

        $response->assertStatus(404); // Based on controller logic returning 404 when repository returns null
        $this->assertEquals('Original review', $review->fresh()->content);
    }

    public function test_user_can_update_own_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        $property = $this->createProperty();
        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Original review',
            'rate' => 5
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated review',
            'rate' => 1
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated review', $review->fresh()->content);
    }

    public function test_admin_can_update_any_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createProperty();
        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Original review',
            'rate' => 5
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated by admin',
            'rate' => 1
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated by admin', $review->fresh()->content);
    }

    public function test_user_cannot_delete_others_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $property = $this->createProperty();
        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Original review',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_user_can_delete_own_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        $property = $this->createProperty();
        $review = Review::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'content' => 'Original review',
            'rate' => 5
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
