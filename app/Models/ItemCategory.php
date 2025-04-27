<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = [
        "category_id",
        "item_id"
    ];

    protected $hidden = [
        "id"
    ];
}
