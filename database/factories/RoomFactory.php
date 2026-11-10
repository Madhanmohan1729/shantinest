<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'name' => 'Bedroom '.fake()->numberBetween(1, 9),
            'description' => fake()->sentence(),
            'bed_type' => 'Queen bed',
            'max_guests' => 2,
            'features' => ['Air conditioning'],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * A room guests cannot request.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
