<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary data
        $this->artisan('db:seed', ['--class' => 'PropertyTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'LocationSeeder']);
    }

    public function test_guest_cannot_update_review()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Updated content',
            'rate' => 5,
            'property_id' => $property->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_review()
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

    public function test_user_cannot_update_others_review()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Malicious update',
            'rate' => 1,
            'property_id' => $property->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_others_review()
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_update_own_review()
    {
        $owner = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Legitimate update',
            'rate' => 4,
            'property_id' => $property->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Legitimate update',
        ]);
    }

    public function test_owner_can_delete_own_review()
    {
        $owner = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_admin_can_delete_any_review()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', [
            'id' => $review->id,
        ]);
    }
}
