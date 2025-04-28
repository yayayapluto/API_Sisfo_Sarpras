<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class CategoryObserver
{
    private $performedBy;

    public function __construct()
    {
        $currentUser = Auth::guard("sanctum")->user();
        $this->performedBy = $currentUser ? $currentUser->username : "system";
    }

    public function created(Category $category)
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Category",
            "entity_id" => $category->id,
            "action" => "create",
            "old_value" => null,
            "new_value" => $category->toJson(),
        ];
        LogActivity::query()->create($logData);
    }

    public function updated(Category $category)
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Category",
            "entity_id" => $category->id,
            "action" => "update",
            "old_value" => json_encode($category->getOriginal()),
            "new_value" => $category,
        ];
        LogActivity::query()->create($logData);
    }

    public function deleted(Category $category)
    {
        $logData = [
            "performed_by" => $this->performedBy,
            "entity" => "Category",
            "entity_id" => $category->id,
            "action" => "delete",
            "old_value" => $category->toJson(),
            "new_value" => null,
        ];
        LogActivity::query()->create($logData);
    }
}
