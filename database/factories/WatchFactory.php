<?php

// Isabela Ruiz

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'brand' => fake()->randomElement(['Casio', 'Seiko', 'Citizen', 'Orient', 'Tissot']),
            'description' => fake()->sentence(12),
            'price' => fake()->numberBetween(80, 900) * 1000,
            'stock' => fake()->numberBetween(1, 20),
            'image' => null,
        ];
    }
}
