<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Rack;
use Illuminate\Http\Request;

class RackController extends Controller
{
    public function index()
    {
        $racksQuery = Rack::query()->with("items");

        if (request()->filled('search')) $racksQuery->where('name', 'LIKE', '%' . request()->search . '%');

        if (request()->filled('minCapacity')) $racksQuery->where('capacity', '>=', request()->minCapacity);

        if (request()->filled('maxCapacity')) $racksQuery->where('capacity', '<=', request()->maxCapacity);

        $sortBy = in_array(request()->sortBy, ['name', 'capacity', 'created_at']) ? request()->sortBy : 'name';
        $sortDir = request()->sortDir === 'asc' ? 'asc' : 'desc';
        $racksQuery->orderBy($sortBy, $sortDir);

        $size = min(max(request()->size ?? 10, 1), 100);
        $racks = $racksQuery->simplePaginate($size);

        return Formatter::apiResponse(200, "Rack list retrieved", $racks);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "name" => "required|string|min:1",
            "capacity" => "required|integer|min:0"
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $newCode = Formatter::makeDash($request->name);
        if (Rack::query()->where("code", $newCode)->exists()) {
            return Formatter::apiResponse(400, "Rack already exists");
        }

        $storeData = $validator->validated();
        $storeData["code"] = $newCode;

        $newRack = Rack::query()->create($storeData);
        return Formatter::apiResponse(200, "Rack created", $newRack);
    }

    public function show(string $slug)
    {
        $rack = Rack::query()->with("items")->where("code", $slug)->first();
        if (!$rack) {
            return Formatter::apiResponse(404, "Rack not found");
        }
        return Formatter::apiResponse(200, "Rack retrieved", $rack);
    }

    public function update(Request $request, string $slug)
    {
        $rack = Rack::query()->where("code", $slug)->first();
        if (!$rack) {
            return Formatter::apiResponse(404, "Rack not found");
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "name" => "sometimes|string|min:1",
            "capacity" => "sometimes|integer|min:0"
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $updateData = $validator->validated();

        if (isset($updateData["name"])) {
            $newCode = Formatter::makeDash($updateData["name"]);
            if (Rack::query()->where("code", $newCode)->where("id", "!=", $rack->id)->exists()) {
                return Formatter::apiResponse(400, "Rack name already used");
            }
            $updateData["code"] = $newCode;
        }

        $rack->update($updateData);

        return Formatter::apiResponse(200, "Rack updated", $rack);
    }

    public function destroy(string $slug)
    {
        $rack = Rack::query()->where("code", $slug)->first();
        if (!$rack) {
            return Formatter::apiResponse(404, "Rack not found");
        }

        $rack->delete();

        return Formatter::apiResponse(200, "Rack deleted");
    }
}
