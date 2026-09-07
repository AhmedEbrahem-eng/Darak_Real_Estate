<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTokenExpirySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_endpoint_rejects_expired_token(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('test-token');
        $plainToken = $tokenResult->plainTextToken;

        // Manually set token expires_at in past
        $tokenResult->accessToken->update([
            'expires_at' => Carbon::now()->subMinutes(10),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->getJson('/api/users/details');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Token has expired.']);
    }

    public function test_user_endpoint_allows_valid_token(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('test-token');
        $plainToken = $tokenResult->plainTextToken;

        $tokenResult->accessToken->update([
            'expires_at' => Carbon::now()->addMinutes(60),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->getJson('/api/users/details');

        $response->assertStatus(200);
    }
}
