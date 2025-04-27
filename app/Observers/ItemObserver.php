<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class ItemObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    public function created(Item $item): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Item",
            "entity_id" => $item->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $item->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    public function updated(Item $item): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Item",
            "entity_id" => $item->id,
            "action" => "update",
            "old_value" => json_encode($item->getOriginal()),
            "new_value" => json_encode($item->getChanges()),
        ];
        LogActivity::query()->create($logData);
    }

    public function deleted(Item $item): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Item",
            "entity_id" => $item->id,
            "action" => "delete",
            "old_value" => json_encode($item->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }
}
