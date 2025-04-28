<?php

namespace App\Observers;

use App\Models\ItemCategory;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class ItemCategoryObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the ItemCategory "created" event.
     */
    public function created(ItemCategory $itemCategory): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "ItemCategory",
            "entity_id" => $itemCategory->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $itemCategory->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the ItemCategory "updated" event.
     */
    public function updated(ItemCategory $itemCategory): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "ItemCategory",
            "entity_id" => $itemCategory->id,
            "action" => "update",
            "old_value" => json_encode($itemCategory->getOriginal()),
            "new_value" => $itemCategory->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the ItemCategory "deleted" event.
     */
    public function deleted(ItemCategory $itemCategory): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "ItemCategory",
            "entity_id" => $itemCategory->id,
            "action" => "delete",
            "old_value" => json_encode($itemCategory->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the ItemCategory "restored" event.
     */
    public function restored(ItemCategory $itemCategory): void
    {
        //
    }

    /**
     * Handle the ItemCategory "force deleted" event.
     */
    public function forceDeleted(ItemCategory $itemCategory): void
    {
        //
    }
}
