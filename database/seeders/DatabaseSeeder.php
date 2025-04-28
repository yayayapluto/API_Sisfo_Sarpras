<?php

namespace Database\Seeders;

use App\Custom\Formatter;
use App\Models\Attachment;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemAttachment;
use App\Models\ItemCategory;
use App\Models\LogActivity;
use App\Models\Rack;
use App\Models\RackItem;
use App\Models\Returning;
use App\Models\ReturningAttachment;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        LogActivity::query()->truncate();


//        User::query()->truncate();
//        User::query()->create([
//            "username" => "admin",
//            "password" => "admin123",
//            "role" => "admin"
//        ]);
//        User::factory(10)->create();
//
        Category::query()->truncate();
        Category::factory(15)->create();

        Rack::query()->truncate();
        Rack::factory(10)->create();

        Item::query()->truncate();
        Item::factory(50)->create();


        Borrowing::query()->truncate();
        Borrowing::factory(100)->create();

        $borrowingIds = Borrowing::query()->pluck("id")->where("status","approved")->shuffle();
        foreach ($borrowingIds as $borrowingId) {
            Returning::query()->create([
                "borrow_id" => $borrowingId,
                "handled_by" => User::query()->pluck("username")->where("role", "admin")->random(),
                "returning_quantity" => fake()->numberBetween(1, 10),
                "note" => fake()->paragraph()
            ]);
        }

        Attachment::query()->truncate();
        Attachment::factory(ceil(rand(0, 1) * Item::query()->count()));

        DB::statement("SET FOREIGN_KEY_CHECKS = 1");
    }
}
