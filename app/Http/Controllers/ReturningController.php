<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Borrowing;
use App\Models\Item;
use App\Models\Returning;
use App\Observers\ReturningObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReturningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $returningQuery = Returning::query()->with(["borrow.item", "handler", "returningAttachments"]);

        $user = Auth::guard("sanctum")->user();
//        if ($user->role === "user") {
//            $returningQuery->join("borrowings", "borrowings.id", "returnings.borrow_id")->where("borrowings.user_id", $user->id);
//        }

        if ($request->filled("status")) $returningQuery = $returningQuery->join("borrowings", "returnings.borrow_id", "borrow_id")->where("borrowings.status", $request->status);

        if ($request->filled('borrowId')) $returningQuery->where('returnings.borrow_id', $request->borrowId);

        if ($request->filled('minQuantity')) $returningQuery->where('returnings.returned_quantity', '>=', $request->minQuantity);

        if ($request->filled('maxQuantity')) $returningQuery->where('returnings.returned_quantity', '<=', $request->maxQuantity);

        if ($request->filled('handledBy')) $returningQuery->where("returnings.handled_by", $request->handledBy);

        $sortBy = in_array($request->sortBy, ['borrow_id', 'returned_quantity', 'created_at']) ? "returnings." . $request->sortBy : 'returnings.created_at';
        $sortDir = $request->sortDir === 'asc' ? 'asc' : 'desc';
        $returningQuery->orderBy($sortBy, $sortDir);

        $size = min(max($request->size ?? 10, 1), 100);
        $returnings = $returningQuery->simplePaginate(10);

        return Formatter::apiResponse(200, "Returning list retrieved", $returnings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "borrowId" => "required|integer|exists:borrowings,id",
            "quantity" => "required|integer|min:1",
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();
        $validated["returned_quantity"] = $validated["quantity"];

        $borrowing = Borrowing::query()->find($validated["borrowId"]);
        if (!$borrowing) {
            return Formatter::apiResponse(404, "Borrow data not found");
        }

        $previousReturnRequest = Returning::query()->where("borrow_id", $validated["borrowId"])->join("borrowings", "borrowings.id", "returnings.borrow_id")->where("status", "pending")->first();
        if ($previousReturnRequest) {
            return Formatter::apiResponse(400, "Previous return request was still pending, please stay tune", $previousReturnRequest);
        }

        $validated["borrow_id"] = $validated["borrowId"];

        $borrowing->update([
            "status" => "pending"
        ]);

        $newReturning = Returning::query()->create($validated);
        return Formatter::apiResponse(200, "Returning request sent, please wait for admin approval", $newReturning);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $returningQuery = Returning::query()->with(["borrow.item","handler","returningAttachments"]);

        $user = Auth::guard("sanctum")->user();
        if ($user->role === "user") {
            $returningQuery = $returningQuery->join("borrowings", "returnings.borrow_id", "borrow_id")->where("borrowings.user_id", $user->id);
        }

        $returning = $returningQuery->where("returnings.id", $id)->first();
//        dd($returning);
        if (is_null($returning)) {
            return Formatter::apiResponse(404, "Returning data not found");
        }

        return Formatter::apiResponse(200, "Returning data found", $returning);
    }

    public function approve(Request $request, int $id)
    {
        $returning = Returning::query()->find($id);
        if (is_null($returning)) {
            return Formatter::apiResponse(404, "Returning data not found");
        }

        $validator = Validator::make($request->all(), [
            "note" => "sometimes|string"
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(200, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();
//        dd($validated);

        $borrowStatus = $returning->borrow->status;
        if ($borrowStatus !== "pending") {
            return Formatter::apiResponse(400, "Cannot approve returning request that not have pending status");
        }

        $borrowItemId = $returning->borrow->item_id;
        $item = Item::query()->find($borrowItemId);
        $item->update([
            "stock" => $item->stock + $returning->returned_quantity
        ]);

        $adminName = Auth::guard("sanctum")->user()->username;

        $validated["handled_by"] = $adminName;

        Borrowing::query()->find($returning->id)->update([
            "status" => "returned"
        ]);
        $returning->update($validated);

        return Formatter::apiResponse(200, "Returning approved", Returning::query()->with(["borrow.item","handler","returningAttachments"])->find($returning->id));
    }

    public function reject(Request $request, int $id)
    {
        $returning = Returning::query()->find($id);
        if (is_null($returning)) {
            return Formatter::apiResponse(404, "Returning data not found");
        }

        $validator = Validator::make($request->all(), [
            "note" => "sometimes|string"
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(200, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();

        $borrowStatus = $returning->borrow->status;
        if ($borrowStatus !== "pending") {
            return Formatter::apiResponse(400, "Cannot approve returning request that not have pending status");
        }

        $adminName = Auth::guard("sanctum")->user()->username;

        $validated["handled_by"] = $adminName;

        Borrowing::query()->find($returning->id)->update([
            "status" => "rejected"
        ]);
            $returning->update($validated);

        return Formatter::apiResponse(200, "Returning rejected", Returning::query()->with(["borrow.item","handler","returningAttachments"])->find($returning->id));
    }
}
