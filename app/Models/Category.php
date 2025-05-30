<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        "slug",
        "name"
    ];

    public function itemCategories()
    {
        return $this->hasMany(ItemCategory::class);
    }

    public function items()
    {
        return $this->hasManyThrough(
            Item::class,
            ItemCategory::class,
            "category_id",
            "id",
            "id",
            "item_id"
        );
    }
}
