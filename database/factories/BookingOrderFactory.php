<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingOrder>
 */
class BookingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('now', '+1 month');
        $checkOut = fake()->dateTimeBetween($checkIn, $checkIn->format('Y-m-d') . ' +7 days');

        return [
            'user_id' => \App\Models\User::factory(),
            'room_type_id' => \App\Models\RoomType::factory(),
            'room_id' => \App\Models\Room::factory(),
            'status' => fake()->randomElement(['pending', 'confirmed', 'checked-in', 'cancelled', 'completed']),
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'phone' => fake()->phoneNumber(),
            'adult' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 2),
            'total_price' => fake()->numberBetween(200, 5000),
            'is_paid' => fake()->boolean(70),
            'is_reviewed' => fake()->boolean(30),
        ];
    }
}
