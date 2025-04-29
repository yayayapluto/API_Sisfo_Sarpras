<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categoriesQuery = Category::query()->with("items");

        if (request()->filled('search')) {
            $categoriesQuery->where('name', 'LIKE', '%' . request()->search . '%');
        }

        $sortBy = in_array(\request()->sortBy, ["name","created_at"]) ? \request()->sortBy : "created_at";
        $sortDir = request()->sortDir === 'asc' ? 'asc' : 'desc';
        $categoriesQuery->orderBy($sortBy, $sortDir);

        $size = min(max(request()->size ?? 10, 1), 100);
        $categories = $categoriesQuery->simplePaginate($size);
        return Formatter::apiResponse(200, "Categories", $categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|min:1"
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $newSlug = Formatter::makeDash($request->name);
        if (Category::query()->where("slug", $newSlug)->exists()) {
            return Formatter::apiResponse(400, "Category already exists");
        }

        $newCategory = Category::query()->create([
            "slug" => $newSlug,
            "name" => $request->name
        ]);
        return Formatter::apiResponse(200, "Category created", $newCategory);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $category = Category::query()->with("items")->where("slug", $slug)->first();
        if (is_null($category)) {
            return Formatter::apiResponse(404, "Category not found");
        }
        return Formatter::apiResponse(200, "Category found", $category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $slug)
    {
        $category = Category::query()->where("slug", $slug)->first();
        if (is_null($category)) {
            return Formatter::apiResponse(404, "Category not found");
        }

        $validator = Validator::make($request->all(), [
            "name" => "sometimes|string|min:1"
        ]);
        if ($validator->fails()) {
            return Formatter::apiResponse(422, "Validation failed", null, $validator->errors()->all());
        }

        $newSlug = $category->slug;
        if ($request->has("name")) {
            $newSlug = Formatter::makeDash($request->name);
            if (Category::query()->where("slug", $newSlug)->exists()) {
                return Formatter::apiResponse(400, "Category already exists");
            }
        }

        $updateData = $validator->validated();
        $updateData["slug"] = $newSlug;

        $category->update($updateData);
        return Formatter::apiResponse(200, "Category updated", Category::query()->find($category->id));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $slug)
    {
        $category = Category::query()->where("slug", $slug)->delete();
        if (!$category) {
            return Formatter::apiResponse(404, "Category not found");
        }
        return Formatter::apiResponse(200, "Category deleted");
    }
}
