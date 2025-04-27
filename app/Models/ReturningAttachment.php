<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturningAttachment extends Model
{
    protected $fillable = [
        "attachment_id",
        "returning_id"
    ];

    protected $hidden = [
        "id"
    ];
}
