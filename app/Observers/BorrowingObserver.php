<?php

namespace App\Observers;

use App\Models\Borrowing;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class BorrowingObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the Borrowing "created" event.
     */
    public function created(Borrowing $borrowing): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Borrowing",
            "entity_id" => $borrowing->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $borrowing->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Borrowing "updated" event.
     */
    public function updated(Borrowing $borrowing): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Borrowing",
            "entity_id" => $borrowing->id,
            "action" => "update",
            "old_value" => json_encode($borrowing->getOriginal()),
            "new_value" => $borrowing,
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the Borrowing "deleted" event.
     */
    public function deleted(Borrowing $borrowing): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Borrowing",
            "entity_id" => $borrowing->id,
            "action" => "delete",
            "old_value" => json_encode($borrowing->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }
}
