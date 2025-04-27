<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = [
        "item_id",
        "quantity",
        "status",
        "approved_at",
        "due",
        "user_id",
        "approved_by"
    ];

    protected $hidden = [
        "id"
    ];
}
