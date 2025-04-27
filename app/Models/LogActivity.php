<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    protected $fillable = [
        "performed_by",
        "entity",
        "entity_id",
        "action",
        "old_value",
        "new_value"
    ];

    protected $hidden = [
        "id"
    ];

    public function performer()
    {
        return $this->belongsTo(User::class, "performed_by", "username");
    }
}
