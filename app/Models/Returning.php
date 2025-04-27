<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returning extends Model
{

    protected $fillable = [
        "borrow_id",
        "handled_by",
        "returning_quantity",
        "note"
    ];

    protected $hidden = [
        "id"
    ];
}
