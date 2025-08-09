<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
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
            'room_type_id' => \App\Models\RoomType::factory(),
            'room_number' => fake()->unique()->numerify('##'),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }
}
