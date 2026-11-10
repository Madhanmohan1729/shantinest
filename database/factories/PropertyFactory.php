<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Property>
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
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'property_type' => 'Entire home',
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'India',
            'max_guests' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 2,
            'amenities' => ['wifi', 'kitchen'],
            'house_rules' => ['No smoking'],
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_published' => true,
        ];
    }

    /**
     * A property visitors cannot see.
     */
    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
