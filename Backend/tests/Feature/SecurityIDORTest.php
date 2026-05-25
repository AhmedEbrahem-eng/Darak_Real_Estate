<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityIDORTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_delete_other_users_property()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $property = Property::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_user_cannot_update_other_users_property()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $property = Property::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user2);

        $response = $this->putJson("/api/properties/{$property->slug}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }
}
