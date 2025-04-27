<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemAttachment extends Model
{
    protected $fillable = [
        "item_id",
        "attachment_id"
    ];

    protected $hidden = [
        "id"
    ];
}
