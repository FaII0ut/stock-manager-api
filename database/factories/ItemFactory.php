<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Items>
 */
class ItemFactory extends Factory
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
            'sku' => fake()->ean8(),
            'description' => fake()->text(),
            'price' => fake()->randomFloat(2, 0, 1000),
            'stock' => fake()->numberBetween(0, 100),
        ];
    }
}
