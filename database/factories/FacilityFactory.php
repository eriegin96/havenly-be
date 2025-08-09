<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $facilities = [
            ['name' => 'Wi-Fi', 'content' => 'wifi-icon', 'description' => 'High-speed wireless internet access'],
            ['name' => 'Air Conditioning', 'content' => 'ac-icon', 'description' => 'Climate controlled room temperature'],
            ['name' => 'Television', 'content' => 'tv-icon', 'description' => 'Flat screen TV with cable channels'],
            ['name' => 'Mini Bar', 'content' => 'minibar-icon', 'description' => 'Stocked mini refrigerator'],
            ['name' => 'Room Service', 'content' => 'service-icon', 'description' => '24/7 in-room dining service'],
        ];

        $facility = fake()->randomElement($facilities);

        return [
            'name' => $facility['name'],
            'content' => $facility['content'],
            'description' => $facility['description'],
        ];
    }
}
