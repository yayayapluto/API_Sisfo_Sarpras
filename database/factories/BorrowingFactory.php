<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Borrowing>
 */
class BorrowingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(["pending","approved","rejected"]);
        $approvedAt = null;
        $approvedBy = null;
        if ($status == "approved") {
            $approvedAt = Carbon::now();
            $approvedBy = User::query()->where("role", "admin")->pluck("username")->random();
        }
        return [
            "item_id" => Item::all()->pluck("id")->random(),
            "quantity" => fake()->numberBetween(1, 10),
            "status" => fake()->randomElement(["pending","approved","rejected","returned"]),
            "approved_at" => $approvedAt,
            "due" => fake()->dateTimeBetween(\Illuminate\Support\Carbon::now(), Carbon::now()->addDays(fake()->numberBetween(1, 7))),
            "user_id" => User::all()->pluck("id")->random(),
            "approved_by" => $approvedBy
        ];
    }
}
