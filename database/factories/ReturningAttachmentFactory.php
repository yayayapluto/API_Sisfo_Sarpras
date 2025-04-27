<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Returning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturningAttachment>
 */
class ReturningAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "attachment_id" => Attachment::all()->pluck("id")->random(),
            "returning_id" => Returning::all()->pluck("id")->random()
        ];
    }
}
