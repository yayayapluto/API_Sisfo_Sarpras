<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    protected $fillable = [
        "sku",
        "name",
        "image_url",
        "stock",
        "barcode_url"
    ];

    public function itemCategories()
    {
        return $this->hasMany(ItemCategory::class);
    }

    public function rackItems()
    {
        return $this->hasMany(RackItem::class);
    }

    public function categories()
    {
        return $this->hasManyThrough(
            Category::class,
            ItemCategory::class,
            "item_id",
            "id",
            "id",
            "category_id");
    }

    public function racks()
    {
        return $this->hasManyThrough(Rack::class, RackItem::class, "item_id", "id", "id", "rack_id");
    }

    public function attachments()
    {
        return $this->hasManyThrough(Attachment::class, ItemAttachment::class, "item_id", "id", "id", "attachment_id");
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }
}
