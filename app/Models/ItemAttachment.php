<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function Symfony\Component\Translation\t;

class ItemAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        "item_id",
        "attachment_id"
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class);
    }
}
