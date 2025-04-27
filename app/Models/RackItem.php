<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "rack_id",
        "item_id"
    ];

    protected $hidden = [
        "id"
    ];

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
