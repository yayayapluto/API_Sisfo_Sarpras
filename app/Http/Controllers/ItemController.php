<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::query()->with(["categories","racks"])->simplePaginate(10);
        return Formatter::apiResponse(200, "Item list retrieved", $items);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "name" => "required|string|min:1",
            "image" => "sometimes|image",
            "stock" => "required|integer|min:0",
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();

        $newSku = Formatter::makeDash(Formatter::removeVowel($validated["name"] . Carbon::now()->toDateString()));
        $validated["sku"] = $newSku;

        if ($request->hasFile("image")) {
            $image = $request->file("image");
            $path = "item-images";
            $fileName = Formatter::makeDash($newSku . " upload " . Carbon::now()->toDateString() . "." . $image->getClientOriginalExtension());
            $storedUrl = $image->storeAs($path, $fileName, "public");
            $validated["image_url"] = url(Storage::url($storedUrl));
        }

        $newItem = Item::query()->create($validated);
        return Formatter::apiResponse(200, "Item created", $newItem);
    }

    public function show(string $sku)
    {
        $item = Item::query()->with(["categories","racks"])->where("sku", $sku)->first();
        if (!$item) {
            return Formatter::apiResponse(404, "Item not found");
        }
        return Formatter::apiResponse(200, "Item retrieved", $item);
    }

    public function update(Request $request, string $sku)
    {
        $item = Item::query()->where("sku", $sku)->first();
        if (!$item) {
            return Formatter::apiResponse(404, "Item not found");
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "name" => "sometimes|string|min:1",
            "stock" => "sometimes|integer|min:0",
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();

        if (isset($validated["name"])) {
            $newSku = Formatter::makeDash(Formatter::removeVowel($validated["name"] . Carbon::now()->toDateString()));
            $validated["sku"] = $newSku;
        }

        $item->update($validated);

        return Formatter::apiResponse(200, "Item updated", Item::query()->find($item->id));
    }

    public function destroy(string $sku)
    {
        $item = Item::query()->where("sku", $sku)->first();
        if (!$item) {
            return Formatter::apiResponse(404, "Item not found");
        }

        $item->delete();

        return Formatter::apiResponse(200, "Item deleted");
    }
}
