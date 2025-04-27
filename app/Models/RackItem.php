<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RackItem extends Model
{
    protected $fillable = [
        "rack_id",
        "item_id"
    ];

    protected $hidden = [
        "id"
    ];
}
