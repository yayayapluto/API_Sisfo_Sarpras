<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\LogActivity;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logActivityQuery = LogActivity::query()->with("performer");

        if (request()->filled("performedBy")) $logActivityQuery->where("performed_by", request()->performedBy);
        if (request()->filled("entity")) $logActivityQuery->where("entity", request()->entity);
        if (request()->filled("action")) $logActivityQuery->where("action", request()->action);

        $sortBy = in_array(request()->sortBy, ['performed_by', 'entity', 'action', 'created_at']) ? request()->sortBy : 'created_at';
        $sortDir = request()->sortDir === 'asc' ? 'asc' : 'desc';

        $logActivityQuery->orderBy($sortBy, $sortDir);

        $size = min(max(request()->size ?? 10, 1), 100);

        $logActivities = $logActivityQuery->simplePaginate($size);
        return Formatter::apiResponse(200, "Log activity list retrieved", $logActivities);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $logActivity = LogActivity::query()->find($id);
        return Formatter::apiResponse(200, "Log activity data retrieved", $logActivity);
    }
}
