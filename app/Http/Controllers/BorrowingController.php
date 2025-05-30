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
        $borrowingsQuery = Borrowing::with(["user","item"]);
        $user = Auth::guard("sanctum")->user();

        if ($user->role === "user") {
            $borrowingsQuery->where("user_id", $user->id);
        } else {
            if ($request->filled('userId')) {
                $borrowingsQuery->where('user_id', $request->userId);
            }
        }

        if ($request->filled('itemId')) $borrowingsQuery->where('item_id', $request->itemId);
        if ($request->filled('minQuantity')) $borrowingsQuery->where('quantity', '>=', $request->minQuantity);
        if ($request->filled('maxQuantity')) $borrowingsQuery->where('quantity', '<=', $request->maxQuantity);
        if ($request->filled('status')) $borrowingsQuery->where('status', $request->status);
        if ($request->filled('approvedAt')) $borrowingsQuery->whereDate('approved_at', $request->approvedAt);
        if ($request->filled('minDue')) $borrowingsQuery->where('due', '>=', $request->minDue);
        if ($request->filled('maxDue')) $borrowingsQuery->where('due', '<=', $request->maxDue);

        if ($request->filled('approvedBy')) $borrowingsQuery->where("approved_by", $request->approvedBy)->with("approver");

        $sortBy = in_array($request->sortBy, ['approved_at','due','created_at'])
            ? $request->sortBy
            : 'created_at';
        $sortDir = $request->sortDir === 'desc' ? 'desc' : 'asc';
        $borrowingsQuery->orderBy($sortBy, $sortDir);

        $size = min(max(request()->size ?? 10, 1), 100);
        return Formatter::apiResponse(200, "Borrowing list retrieved",
            $borrowingsQuery->simplePaginate($size));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "item" => "required|string|exists:items,sku",
            "quantity" => "required|integer|min:1",
            "due" => "required|date"
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();

        $item = Item::query()->where("sku", $validated["item"])->select(["id","stock"])->first();
        if (is_null($item->id)) {
            return Formatter::apiResponse(404, "Item not found");
        }

        $pendingBorrowRequestExists = Borrowing::query()->where("user_id", Auth::guard("sanctum")->user()->id)->join("items", "items.id", "borrowings.item_id")->where("status", "pending")->first();
        if ($pendingBorrowRequestExists) {
            return Formatter::apiResponse(400, "You already requested for this item, please wait previous request checked by admin, stay tune", $pendingBorrowRequestExists);
        }

        $itemStock = $item->stock;
        $borrowQuantity = $validated["quantity"];
        if ($itemStock - $borrowQuantity < 0) {
            return Formatter::apiResponse(400, "Cannot borrow more than item stock", [
                "remainingItemStock" => $itemStock,
                "borrowQuantity" => $borrowQuantity
            ]);
        }

        $validated["user_id"] = Auth::guard("sanctum")->user()->id;
        $validated["item_id"] = $item->id;

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

        $item = Item::query()->find($borrowing->item_id);
        $item->update([
            "stock" => $item->stock - $borrowing->quantity
        ]);
        return Formatter::apiResponse(200, "Borrowing approved", Borrowing::query()->find($id)->load("item"));
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
