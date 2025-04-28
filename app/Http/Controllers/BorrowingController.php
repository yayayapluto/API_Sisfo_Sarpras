<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Borrowing;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $borrowingsQuery = Borrowing::query()->with(["user","item","approver"]);

        $user = Auth::guard("sanctum")->user();
        if ($user->role === "user") {
            $borrowingsQuery = $borrowingsQuery->where("user_id", $user->id);
        }

        $borrowings = $borrowingsQuery->simplePaginate(10);
        return Formatter::apiResponse(200, "Borrowing list retrieved", $borrowings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "item" => "required|string|exists:items,sku",
            "quantity" => "required|integer|min:1",
            "due" => "required|datetime"
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();

        $itemId = Item::query()->where("sku", $validated["sku"])->pluck("id")->first();
        if (is_null($itemId)) {
            return Formatter::apiResponse(404, "Item not found");
        }

        $validated["user_id"] = Auth::guard("sanctum")->user()->id;
        $validated["item_id"] = $itemId;

        $newBorrowing = Borrowing::query()->create($validated)->load(["item"]);
        return Formatter::apiResponse(200, "Borrow request sent, please wait for the approver", $newBorrowing);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $borrowingQuery = Borrowing::query()->with(["user","item","approver"]);

        $user = Auth::guard("sanctum")->user();
        if ($user->role === "user") {
            $borrowingQuery = $borrowingQuery->where("user_id", $user->id);
        }

        $borrowing = $borrowingQuery->find($id);

        if (is_null($borrowing)) {
            return Formatter::apiResponse(404, "Borrowing data not found");
        }
        return Formatter::apiResponse(200, "Borrowing data found", $borrowing);
    }

    public function approve(int $id)
    {
        $borrowing = Borrowing::query()->find($id);
        if (is_null($borrowing)) {
            return Formatter::apiResponse(404, "Borrowing data not found");
        }
        if ($borrowing->status !== "pending") {
            return Formatter::apiResponse(400, "Cannot approve borrowing request that not have pending status");
        }

        $adminName = Auth::guard("sanctum")->user()->username;
        $borrowing->update([
            "status" => "approved",
            "approved_at" => Carbon::now(),
            "approved_by" => $adminName
        ]);
        return Formatter::apiResponse(200, "Borrowing approved", Borrowing::query()->find($id));
    }

    public function reject(Request $request, int $id)
    {
        $borrowing = Borrowing::query()->find($id);
        if (is_null($borrowing)) {
            return Formatter::apiResponse(404, "Borrowing data not found");
        }
        if ($borrowing->status !== "pending") {
            return Formatter::apiResponse(400, "Cannot reject borrowing request that not have pending status");
        }

        $borrowing->update([
            "status" => "rejected",
        ]);
        return Formatter::apiResponse(200, "Borrowing rejected", Borrowing::query()->find($id));
    }
}
