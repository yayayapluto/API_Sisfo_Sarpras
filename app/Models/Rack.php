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

    public function rackItems()
    {
        return $this->hasOne(RackItem::class);
    }

    public function items()
    {
        return $this->hasManyThrough(Item::class, RackItem::class, "rack_id", "id");
    }
}
