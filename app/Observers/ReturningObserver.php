<?php

namespace App\Observers;

use App\Models\LogActivity;
use App\Models\Returning;
use Illuminate\Support\Facades\Auth;

class ReturningObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the Returning "created" event.
     */
    public function created(Returning $returning): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Returning",
            "entity_id" => $returning->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $returning->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Returning "updated" event.
     */
    public function updated(Returning $returning): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Returning",
            "entity_id" => $returning->id,
            "action" => "update",
            "old_value" => $returning->toJson(),
            "new_value" => json_encode($returning->getChanges()),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Returning "deleted" event.
     */
    public function deleted(Returning $returning): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Returning",
            "entity_id" => $returning->id,
            "action" => "delete",
            "old_value" => json_encode($returning->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }
}
