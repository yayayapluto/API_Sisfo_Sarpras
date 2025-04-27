<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        "file_url",
        "file_type"
    ];

    protected $hidden = [
        "id"
    ];

    public function itemAttachments()
    {
        return $this->hasMany(ItemAttachment::class);
    }

    public function returningAttachment()
    {
        return $this->hasMany(ReturningAttachment::class);
    }
}
