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


        User::query()->truncate();
        User::query()->create([
            "username" => "admin",
            "password" => "admin123",
            "role" => "admin"
        ]);
        User::factory(10)->create();

        Item::query()->truncate();

        Category::query()->truncate();
        Category::factory(15)->create();
        $categoryIds = Category::all()->pluck("id")->shuffle();
        foreach ($categoryIds as $categoryId) {
            $itemCount = fake()->numberBetween(1, 10);
            for ($i = 0; $i < $itemCount; $i++) {
                $name = $i . "-Item" . fake()->unique()->words(3, true);
                $newItem = Item::query()->create([
                    "sku" => Formatter::makeDash(Formatter::removeVowel($name) . "-"  . Carbon::now()->toDateString()) ,
                    "name" => $name,
                    "image_url" => "image url here",
                    "stock" => fake()->numberBetween(1, 100),
                    "barcode_url" => "barcode url here"
                ]);
                $attachmentCount = fake()->numberBetween(1, 5);
                for ($k = 0; $k < $attachmentCount; $k++) {
                    $newAttachment = Attachment::query()->create([
                        "file_url" => "file url",
                        "file_type" => "file type"
                    ]);
                    ItemAttachment::query()->create([
                        "item_id" => $newItem->id,
                        "attachment_id" => $newAttachment->id
                    ]);
                }
                if (!ItemCategory::query()->where("item_id", $newItem->id)->where("category_id", $categoryId)->exists()){
                    ItemCategory::query()->create([
                        "category_id" => $categoryId,
                        "item_id" => $newItem->id
                    ]);
                }
            }
        }


        Rack::query()->truncate();
        Rack::factory(10)->create();
        $rackIds = Rack::all()->pluck("id")->shuffle();
        foreach ($rackIds as $rackId) {
            $itemCount = fake()->numberBetween(1, 10);
            for ($i = 0; $i < $itemCount; $i++) {
                $name = $i . "-Item" . fake()->unique()->words(3, true);
                $newItem = Item::query()->create([
                    "sku" => Formatter::makeDash(Formatter::removeVowel($name) . "-"  . Carbon::now()->toDateString()) ,
                    "name" => $name,
                    "image_url" => "image url here",
                    "stock" => fake()->numberBetween(1, 100),
                    "barcode_url" => "barcode url here"
                ]);
                $attachmentCount = fake()->numberBetween(1, 5);
                for ($k = 0; $k < $attachmentCount; $k++) {
                    $newAttachment = Attachment::query()->create([
                        "file_url" => "file url",
                        "file_type" => "file type"
                    ]);
                    ItemAttachment::query()->create([
                        "item_id" => $newItem->id,
                        "attachment_id" => $newAttachment->id
                    ]);
                }
                if (!RackItem::query()->where("item_id", $newItem->id)->where("rack_id", $rackId)->exists()){
                    RackItem::query()->create([
                        "rack_id" => $rackId,
                        "item_id" => $newItem->id
                    ]);
                }
            }
        }

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
                        "returned_quantity" => fake()->numberBetween(0, $newItem->quantity)
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

        DB::statement("SET FOREIGN_KEY_CHECKS = 1");
    }
}
