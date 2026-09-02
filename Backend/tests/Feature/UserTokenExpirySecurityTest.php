<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTokenExpirySecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_endpoints_reject_expired_tokens(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        // Manually expire the token in the personal_access_tokens table
        $tokenResult->accessToken->expires_at = \Carbon\Carbon::now()->subMinutes(10);
        $tokenResult->accessToken->save();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users/details');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Token has expired.']);
    }

    public function test_forged_token_secret_is_not_deleted_or_accepted(): void
    {
        $user = User::factory()->create();
        $tokenResult = $user->createToken('auth_token');
        $tokenResult->accessToken->expires_at = \Carbon\Carbon::now()->subMinutes(10);
        $tokenResult->accessToken->save();

        // Send forged secret for token id
        $forgedToken = $tokenResult->accessToken->id . '|forged_secret_12345';

        $response = $this->withHeader('Authorization', 'Bearer ' . $forgedToken)
            ->getJson('/api/users/details');

        $response->assertStatus(401);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenResult->accessToken->id,
        ]);
    }
}
