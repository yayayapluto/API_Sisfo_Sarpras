<?php

namespace App\Observers;

use App\Models\LogActivity;
use App\Models\RackItem;
use Illuminate\Support\Facades\Auth;

class RackItemObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the RackItem "created" event.
     */
    public function created(RackItem $rackItem): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "RackItem",
            "entity_id" => $rackItem->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $rackItem->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the RackItem "updated" event.
     */
    public function updated(RackItem $rackItem): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "RackItem",
            "entity_id" => $rackItem->id,
            "action" => "update",
            "old_value" => json_encode($rackItem->getOriginal()),
            "new_value" => $rackItem->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the RackItem "deleted" event.
     */
    public function deleted(RackItem $rackItem): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "RackItem",
            "entity_id" => $rackItem->id,
            "action" => "delete",
            "old_value" => json_encode($rackItem->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the RackItem "restored" event.
     */
    public function restored(RackItem $rackItem): void
    {
        //
    }

    /**
     * Handle the RackItem "force deleted" event.
     */
    public function forceDeleted(RackItem $rackItem): void
    {
        //
    }
}
