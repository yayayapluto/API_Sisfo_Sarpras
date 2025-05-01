<?php

namespace App\Observers;

use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "User",
            "entity_id" => $user->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $user->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "User",
            "entity_id" => $user->id,
            "action" => "update",
            "old_value" => $user->toJson(),
            "new_value" => json_encode($user->getChanges()),
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "User",
            "entity_id" => $user->id,
            "action" => "delete",
            "old_value" => json_encode($user->getOriginal()),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
