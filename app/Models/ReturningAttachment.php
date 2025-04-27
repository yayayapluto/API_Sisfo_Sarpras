<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturningAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        "attachment_id",
        "returning_id"
    ];

    protected $hidden = [
        "id"
    ];

    public function attachment()
    {
        return $this->belongsTo(Attachment::class);
    }

    public function returning()
    {
        return $this->belongsTo(Returning::class);
    }
}
