<?php

namespace Database\Factories;

use App\Custom\Formatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
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
        $name = "Item " . fake()->unique()->words(3, true);
        return [
            "sku" => Formatter::makeDash(Formatter::removeVowel($name) . Carbon::now()->toDateString()) ,
            "name" => $name,
            "image_url" => "image url here",
            "stock" => fake()->numberBetween(1, 100),
            "barcode_url" => "barcode url here"
        ];
    }
}
