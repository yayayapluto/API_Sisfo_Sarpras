<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        "file_url",
        "file_type"
    ];

    protected $hidden = [
        "id"
    ];
}
