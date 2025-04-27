<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Rack;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RackItem>
 */
class RackItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "rack_id" => Rack::query()->inRandomOrder()->pluck("id")->first(),
            "item_id" => Item::query()->inRandomOrder()->pluck("id")->first()
        ];
    }
}
