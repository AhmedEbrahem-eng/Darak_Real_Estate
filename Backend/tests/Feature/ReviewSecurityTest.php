<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function createDependencies()
    {
        $user = User::factory()->create();
        $location = \App\Models\Location::create([
            'city' => 'Test City',
            'street' => 'Test Street',
            'country' => 'Test Country',
            'state' => 'Test State'
        ]);
        $propertyType = \App\Models\PropertyType::create(['name' => 'Test Type']);
        $property = Property::factory()->create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
        return [$user, $property];
    }

    public function test_unauthenticated_user_cannot_update_review()
    {
        [$user, $property] = $this->createDependencies();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated comment',
            'rate' => 5,
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_review()
    {
        [$user, $property] = $this->createDependencies();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_another_users_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        [$landlord, $property] = $this->createDependencies();
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated comment',
            'rate' => 5,
        ]);

        // Should return 403 Forbidden or 404 Not Found if we want to hide existence
        // Currently it likely returns 200 because there is no check.
        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        [$landlord, $property] = $this->createDependencies();
        $review = Review::factory()->create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_update_their_own_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        [$landlord, $property] = $this->createDependencies();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated comment',
            'rate' => 5,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated comment', $review->fresh()->content);
    }

    public function test_owner_can_delete_their_own_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        [$landlord, $property] = $this->createDependencies();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_admin_can_delete_any_review()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        [$landlord, $property] = $this->createDependencies();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
