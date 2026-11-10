<?php

namespace Database\Factories;

use App\Enums\StayRequestStatus;
use App\Models\Property;
use App\Models\StayRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StayRequest>
 */
class StayRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = now()->addDays(fake()->numberBetween(5, 30))->startOfDay();

        return [
            'property_id' => Property::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('98########'),
            'guests' => 2,
            'check_in' => $checkIn,
            'check_out' => $checkIn->addDays(2),
            'message' => fake()->sentence(),
            'status' => StayRequestStatus::Pending,
        ];
    }

    /**
     * A request an admin has already approved.
     */
    public function approved(): static
    {
        return $this->state(fn (): array => ['status' => StayRequestStatus::Approved, 'responded_at' => now()]);
    }

    /**
     * A request an admin has already declined.
     */
    public function declined(): static
    {
        return $this->state(fn (): array => ['status' => StayRequestStatus::Declined, 'responded_at' => now()]);
    }
}
