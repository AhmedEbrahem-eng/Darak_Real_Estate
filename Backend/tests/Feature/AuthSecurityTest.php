<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Log;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that generic error message is returned when registration fails due to internal error.
     */
    public function test_generic_error_message_on_registration_failure()
    {
        // Mock the User model to throw an exception during creation
        // Note: This is a bit tricky with Eloquent's static methods, but we can mock the underlying database call or use partial mocks if supported.
        // For simplicity, let's just verify the controller code as we already did.
        // Actually, let's try to mock the User model.

        $this->partialMock(User::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new \Exception('Sensitive database error details'));
        });

        Log::shouldReceive('error')->once();

        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01011112222',
            'role' => 'user',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'An internal server error occurred.',
        ]);
        $response->assertJsonMissing(['Sensitive database error details']);
    }
}
