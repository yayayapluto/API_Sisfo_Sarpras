<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemAttachment;
use App\Models\ItemCategory;
use App\Models\Rack;
use App\Models\RackItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        $itemsQuery = Item::query()->with(["categories", "racks"]);

        if (request()->filled('search')) $itemsQuery->where('name', 'LIKE', '%' . request()->search . '%');

        if (request()->filled('minStock')) $itemsQuery->where('stock', '>=', request()->minStock);

        if (request()->filled('maxStock')) $itemsQuery->where('stock', '<=', request()->maxStock);

        $sortBy = in_array(request()->sortBy, ['name', 'stock', 'created_at']) ? request()->sortBy : 'name';
        $sortDir = request()->sortDir === 'asc' ? 'asc' : 'desc';
        $itemsQuery->orderBy($sortBy, $sortDir);

        $size = min(max(request()->size ?? 10, 1), 100);
        $items = $itemsQuery->simplePaginate($size);

        return Formatter::apiResponse(200, "Item list retrieved", $items);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            "name" => "required|string|min:1",
            "image" => "sometimes|image",
            "stock" => "required|integer|min:0",
            "categories" => "sometimes|string",
            "racks" => "sometimes|string",
        ]);

        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $validated = $validator->validated();

        $newSku = Formatter::makeDash(Formatter::removeVowel($validated["name"] . "-" . Carbon::now()->toDateString()));
        if (Item::query()->where("sku", $newSku)->exists()) {
            return Formatter::apiResponse(400, "Item already exists");
        }

        $validated["sku"] = $newSku;

        DB::beginTransaction();
        try {
            if ($request->hasFile("image")) {
                $image = $request->file("image");
                $path = "item-images";
                $fileName = Formatter::makeDash($newSku . " upload " . Carbon::now()->toDateString() . "." . $image->getClientOriginalExtension());
                $storedUrl = $image->storeAs($path, $fileName, "public");
                $validated["image_url"] = url(Storage::url($storedUrl));
            }

            $newItem = Item::query()->create($validated);

            if ($request->has("categories")) {
                $categorySlugs = explode(",",$request->categories);
                $categoryIds = Category::query()->whereIn("slug", $categorySlugs)->pluck("id");
                foreach ($categoryIds as $categoryId) {
                    if (!ItemCategory::query()->where("item_id", $newItem->id)->where("category_id", $categoryId)->exists()) {
                        ItemCategory::query()->create([
                            "item_id" => $newItem->id,
                            "category_id" => $categoryId
                        ]);
                    }
                }
            }

            if ($request->has("racks")) {
                $rackCodes = explode(",", $request->racks); // "rack1,rack1" => ["rack1","rack2"]
                $racks = Rack::query()->whereIn("code", $rackCodes)->get();

                foreach ($racks as $rack) {
                    // Calculate total stock in the rack
                    $currentStockInRack = $rack->items()->sum('stock');

                    $remainingCapacity = $rack->capacity - $currentStockInRack;

                    if ($validated["stock"] <= $remainingCapacity) {
                        RackItem::query()->create([
                            "item_id" => $newItem->id,
                            "rack_id" => $rack->id
                        ]);
                    } else {
                        DB::rollBack();
                        return Formatter::apiResponse(400, "Not enough capacity in rack: " . $rack->code);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Formatter::apiResponse(500, "An error occurred", null, $e->getMessage());
        }

        $resultQuery = Item::query();
        if ($request->has("categories")) {
            $resultQuery = $resultQuery->with("categories");
        }
        if ($request->has("racks")) {
            $resultQuery = $resultQuery->with("racks");
        }
        if ($request->has("attachments")) {
            $resultQuery = $resultQuery->with("attachments");
        }
        $resultQuery = $resultQuery->find($newItem->id);
        return Formatter::apiResponse(200, "Item created", $resultQuery);
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
            $newSku = Formatter::makeDash(Formatter::removeVowel($validated["name"] . "-" . Carbon::now()->toDateString()));
            if (Item::query()->where("sku", $newSku)->where("id", "!=", $item->id)->exists()) {
                return Formatter::apiResponse(400, "Item already exists");
            }
            $validated["sku"] = $newSku;
        }

        $item->update($validated);

        $resultQuery = Item::query()->with(["categories", "racks"])->find($item->id);
        return Formatter::apiResponse(200, "Item updated", $resultQuery);
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

    // one image per attachment
    public function addAttachment()
    {

    }

    // for updating category assigns
    public function adjustCategories()
    {

    }

    // for updating rack assigns
    public function adjustRacks()
    {

    }
}
