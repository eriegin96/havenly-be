<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $features = [
            ['name' => 'Balcony', 'content' => 'balcony-icon'],
            ['name' => 'Ocean View', 'content' => 'ocean-view-icon'],
            ['name' => 'City View', 'content' => 'city-view-icon'],
            ['name' => 'Garden View', 'content' => 'garden-view-icon'],
            ['name' => 'Kitchen', 'content' => 'kitchen-icon'],
            ['name' => 'Living Room', 'content' => 'living-room-icon'],
            ['name' => 'Separate Bedroom', 'content' => 'bedroom-icon'],
        ];

        $feature = fake()->randomElement($features);

        return [
            'name' => $feature['name'],
            'content' => $feature['content'],
        ];
    }
}
