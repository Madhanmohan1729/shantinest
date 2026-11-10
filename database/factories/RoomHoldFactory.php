<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomHold;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomHold>
 */
class RoomHoldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()->addDays(fake()->numberBetween(1, 20))->setTime(14, 0);

        return [
            'room_id' => Room::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addDays(2)->setTime(11, 0),
            'guest_name' => fake()->name(),
            'note' => null,
        ];
    }
}
