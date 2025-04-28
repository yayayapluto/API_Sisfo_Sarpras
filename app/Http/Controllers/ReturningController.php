<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Borrowing;
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
        /**
         * query params:
         * - borrowId
         * - minReturnedQuantity
         * - maxReturnedQuantity
         * - handledBy
         * - sortBy
         * - sortDir
         * - size
         */

        $returningQuery = Returning::query()->with(["borrow.item","handler","returningAttachments"]);

        $user = Auth::guard("sanctum")->user();
        if ($user->role === "user") {
            $returningQuery = $returningQuery->join("borrowings", "returnings.borrow_id", "borrow_id")->where("borrowings.user_id", $user->id);
        }

        $status = $request->query("status");
        if (!is_null($status)) {
            $returningQuery = $returningQuery->join("borrowings", "returnings.borrow_id", "borrow_id")->where("borrowings.status", $status);
        }

        $returnings = $returningQuery->simplePaginate(10);
        return Formatter::apiResponse(200, "Returning list retrieved", $returnings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "borrowId" => "required|string|exists:borrowings,id",
            "quantity" => "required|integer|min:1",
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();
        $validated["returned_quantity"] = $validated["quantity"];

        Borrowing::query()->where("borrow_id", $validated["borrowId"])->first()->update([
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
        dd($returning);
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

        $borrowStatus = $returning->borrow->status;
        if ($borrowStatus !== "pending") {
            return Formatter::apiResponse(400, "Cannot approve returning request that not have pending status");
        }

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
