<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    use HasFactory;

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

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function returning()
    {
        return $this->hasOne(Returning::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, "approved_by", "username");
    }
}
