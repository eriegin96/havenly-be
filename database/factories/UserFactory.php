<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'dob' => fake()->date('Y-m-d', '2000-01-01'),
            'avatar' => 'chill-guy.png',
            'password' => 'password123', // This will be hashed by the model
            'role' => fake()->randomElement(['user', 'admin']),
            'status' => fake()->numberBetween(0, 1),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a regular user.
     */
    public function user(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'user',
        ]);
    }
}
