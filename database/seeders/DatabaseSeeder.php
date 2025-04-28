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
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0");
        LogActivity::query()->truncate();


        User::query()->truncate();
        User::query()->create([
            "username" => "admin",
            "password" => Hash::make("admin123"),
            "role" => "admin"
        ]);
        User::query()->create([
            "username" => "user",
            "password" => Hash::make("user123"),
            "role" => "user"
        ]);
        User::factory(1)->create();

        Item::query()->truncate();
        Item::factory(20)->create();

        Category::query()->truncate();
        Category::factory(10)->create();

        Rack::query()->truncate();
        Rack::factory(5)->create();

        ItemCategory::query()->truncate();
        ItemCategory::factory(50)->create();

        RackItem::query()->truncate();
        RackItem::factory(100)->create();

        Borrowing::query()->truncate();
        Returning::query()->truncate();
        $userIds = User::all()->pluck("id")->shuffle();
        foreach ($userIds as $userId) {
            $borrowCount = fake()->numberBetween(1, 20);
            for ($j = 0; $j < $borrowCount; $j++) {
                $status = fake()->randomElement(["pending","approved","rejected","returned"]);
                $approvedAt = null;
                $approvedBy = null;
                if ($status == "approved") {
                    $approvedAt = Carbon::now();
                    $approvedBy = User::query()->where("role", "admin")->pluck("username")->random();
                }
                $newBorrowing = Borrowing::query()->create([
                    "item_id" => Item::all()->pluck("id")->random(),
                    "quantity" => fake()->numberBetween(1, 10),
                    "status" => fake()->randomElement(["pending","approved","rejected","returned"]),
                    "approved_at" => $approvedAt,
                    "due" => fake()->dateTimeBetween(\Illuminate\Support\Carbon::now(), Carbon::now()->addDays(fake()->numberBetween(1, 7))),
                    "user_id" => $userId,
                    "approved_by" => $approvedBy
                ]);
                if ($status === "rejected" || "returned") {
                    $newReturning = Returning::query()->create([
                        "borrow_id" => $newBorrowing->id,
                        "handled_by" => $status === "returned" || "rejected" ? "admin" : null,
                        "note" => fake()->paragraph(),
                        "returned_quantity" => fake()->numberBetween(0, $newBorrowing->quantity)
                    ]);
                    $attachmentCount = fake()->numberBetween(1, 5);
                    for ($k = 0; $k < $attachmentCount; $k++) {
                        $newAttachment = Attachment::query()->create([
                            "file_url" => "file url",
                            "file_type" => "file type"
                        ]);
                        ReturningAttachment::query()->create([
                            "returning_id" => $newReturning->id,
                            "attachment_id" => $newAttachment->id
                        ]);
                    }
                }
             }
        }

        Attachment::query()->truncate();
        ItemAttachment::query()->truncate();
        DB::statement("SET FOREIGN_KEY_CHECKS = 1");
    }
}
