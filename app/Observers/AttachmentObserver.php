<?php

namespace App\Observers;

use App\Models\Attachment;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class AttachmentObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the Attachment "created" event.
     */
    public function created(Attachment $attachment): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Attachment",
            "entity_id" => $attachment->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $attachment->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Attachment "updated" event.
     */
    public function updated(Attachment $attachment): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Attachment",
            "entity_id" => $attachment->id,
            "action" => "update",
            "old_value" => json_encode($attachment->getOriginal()),
            "new_value" => $attachment->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Attachment "deleted" event.
     */
    public function deleted(Attachment $attachment): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Attachment",
            "entity_id" => $attachment->id,
            "action" => "delete",
            "old_value" => json_encode($attachment->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Attachment "restored" event.
     */
    public function restored(Attachment $attachment): void
    {
        //
    }

    /**
     * Handle the Attachment "force deleted" event.
     */
    public function forceDeleted(Attachment $attachment): void
    {
        //
    }
}
