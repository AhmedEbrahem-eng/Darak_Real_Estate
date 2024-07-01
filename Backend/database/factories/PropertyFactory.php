<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'slug' => Str::slug($this->faker->unique()->sentence),
            'description' => $this->faker->paragraph,
            'num_of_rooms' => $this->faker->numberBetween(1, 10),
            'num_of_bathrooms' => $this->faker->numberBetween(1, 5),
            'area' => $this->faker->randomFloat(2, 50, 500),
            'price' => $this->faker->numberBetween(100000, 10000000),
            'location_id' => Location::pluck('id')->random(),
            'property_type_id' => PropertyType::pluck('id')->random(),
            'user_id' => User::pluck('id')->random(),
            'availability' => $this->faker->randomElement(['available', 'unavailable']),
            'listing_type' => $this->faker->randomElement(['rent', 'buy']),
        ];
    }
}
