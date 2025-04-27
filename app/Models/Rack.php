<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    /** @use HasFactory<\Database\Factories\RackFactory> */
    use HasFactory;

    protected $fillable = [
        "code",
        "name",
        "capacity"
    ];

    protected $hidden = [
        "id"
    ];
}
