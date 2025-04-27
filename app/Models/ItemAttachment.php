<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        "item_id",
        "attachment_id"
    ];

    protected $hidden = [
        "id"
    ];
}
