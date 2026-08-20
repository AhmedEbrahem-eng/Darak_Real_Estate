<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PropertyType;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Mockery\MockInterface;
use Tests\TestCase;

class PropertyErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_store_exception_returns_generic_error_and_logs()
    {
        Log::spy();

        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $propertyType = PropertyType::create(['name' => 'Apartment']);

        $mockRepo = $this->mock(PropertyRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('createProperty')
                ->once()
                ->andThrow(new \Exception('Database connection failed or sensitive internal detail: secret_db_password'));
        });

        $payload = [
            'title' => 'Unique Property Title',
            'description' => 'Property Description',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120.5,
            'price' => 250000,
            'property_type_id' => $propertyType->id,
            'listing_type' => 'buy',
            'city' => 'Sample City',
            'state' => 'Sample State',
            'street' => '123 Sample St',
            'images' => [UploadedFile::fake()->image('property.jpg')],
        ];

        $response = $this->postJson('/api/properties', $payload);

        $response->assertStatus(500)
            ->assertJson([
                'message' => 'Failed to add property',
                'error' => 'An error occurred while adding the property',
            ]);

        $response->assertJsonMissing(['error' => 'Database connection failed or sensitive internal detail: secret_db_password']);

        Log::shouldHaveReceived('error')
            ->once();
    }
}
