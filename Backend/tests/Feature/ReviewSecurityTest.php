<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic data
        PropertyType::factory()->create();
        Location::factory()->create();
    }

    /** @test */
    public function a_user_cannot_update_another_users_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $user1->id]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Original comment',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Malicious update',
            'rate' => 1
        ]);

        // This should fail with 403 Forbidden
        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Original comment'
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $user1->id]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Original comment',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        // This should fail with 403 Forbidden
        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function an_admin_can_delete_any_review()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $user1->id]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Original comment',
            'rate' => 5
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
