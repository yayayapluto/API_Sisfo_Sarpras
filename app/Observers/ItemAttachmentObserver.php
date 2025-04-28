<?php

namespace App\Observers;

use App\Models\ItemAttachment;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class ItemAttachmentObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the ItemAttachment "created" event.
     */
    public function created(ItemAttachment $itemAttachment): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "ItemAttachment",
            "entity_id" => $itemAttachment->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $itemAttachment->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the ItemAttachment "updated" event.
     */
    public function updated(ItemAttachment $itemAttachment): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "ItemAttachment",
            "entity_id" => $itemAttachment->id,
            "action" => "update",
            "old_value" => json_encode($itemAttachment->getOriginal()),
            "new_value" => $itemAttachment->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the ItemAttachment "deleted" event.
     */
    public function deleted(ItemAttachment $itemAttachment): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "ItemAttachment",
            "entity_id" => $itemAttachment->id,
            "action" => "delete",
            "old_value" => json_encode($itemAttachment->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the ItemAttachment "restored" event.
     */
    public function restored(ItemAttachment $itemAttachment): void
    {
        //
    }

    /**
     * Handle the ItemAttachment "force deleted" event.
     */
    public function forceDeleted(ItemAttachment $itemAttachment): void
    {
        //
    }
}
