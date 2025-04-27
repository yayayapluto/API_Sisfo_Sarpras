<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemAttachment>
 */
class ItemAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "item_id" => Item::all()->pluck("id")->random(),
            "attachment_id" => Attachment::all()->pluck("id")->random()
        ];
    }
}
