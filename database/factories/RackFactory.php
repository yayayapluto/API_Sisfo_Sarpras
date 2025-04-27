<?php

namespace Database\Factories;

use App\Custom\Formatter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rack>
 */
class RackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = "Rack " . fake()->unique()->words(2, true);
        return [
            "code" => Formatter::makeDash($name),
            "name" => $name,
            "capacity" => fake()->numberBetween(10, 100)
        ];
    }
}
