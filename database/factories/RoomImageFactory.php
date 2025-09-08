<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomImage>
 */
class RoomImageFactory extends Factory
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
            'path' => 'storage/images/rooms/room-1.jpg',
            'is_thumbnail' => fake()->boolean(20), // 20% chance of being thumbnail
        ];
    }
}
