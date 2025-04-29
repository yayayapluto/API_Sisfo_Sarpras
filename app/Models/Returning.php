<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returning extends Model
{

    protected $fillable = [
        "borrow_id",
        "handled_by",
        "returned_quantity",
        "note"
    ];

    public function borrow()
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, "handled_by", "username");
    }

    public function returningAttachments()
    {
        return $this->hasMany(ReturningAttachment::class);
    }
}
