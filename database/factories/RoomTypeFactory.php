<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roomTypes = ['Deluxe Room', 'Premium Room', 'Suite Room', 'Presidential Suite'];

        return [
            'name' => fake()->randomElement($roomTypes),
            'area' => fake()->numberBetween(25, 100),
            'price' => fake()->numberBetween(100, 1000),
            'quantity' => fake()->numberBetween(5, 20),
            'adult' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 2),
            'description' => fake()->paragraph(3),
        ];
    }
}
