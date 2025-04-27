<?php

namespace App\Observers;

use App\Models\LogActivity;
use App\Models\Rack;
use Illuminate\Support\Facades\Auth;

class RackObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the Rack "created" event.
     */
    public function created(Rack $rack): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Rack",
            "entity_id" => $rack->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $rack->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Rack "updated" event.
     */
    public function updated(Rack $rack): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Rack",
            "entity_id" => $rack->id,
            "action" => "update",
            "old_value" => json_encode($rack->getOriginal()),
            "new_value" => json_encode($rack->getChanges()),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Rack "deleted" event.
     */
    public function deleted(Rack $rack): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Rack",
            "entity_id" => $rack->id,
            "action" => "delete",
            "old_value" => json_encode($rack->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }
}
