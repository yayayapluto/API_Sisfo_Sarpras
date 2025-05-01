<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->group(function () {
   Route::post("login", [\App\Http\Controllers\AuthController::class, "login"]);
   Route::get("logout", [\App\Http\Controllers\AuthController::class, "logout"])->middleware("need-token");
   Route::get("self", [\App\Http\Controllers\AuthController::class, "self"])->middleware("need-token");
});

Route::middleware("need-token")->group(function () {

   Route::prefix("admin")->middleware("role:admin")->group(function () {
       Route::prefix("dashboard")->group(function () {
           Route::get("general", [\App\Http\Controllers\DashboardController::class, "general"]);
           Route::get("user", [\App\Http\Controllers\DashboardController::class, "user"]);
           Route::get("rack", [\App\Http\Controllers\DashboardController::class, "rack"]);
           Route::get("category", [\App\Http\Controllers\DashboardController::class, "category"]);
           Route::get("item", [\App\Http\Controllers\DashboardController::class, "item"]);
           Route::get("borrowing", [\App\Http\Controllers\DashboardController::class, "borrowing"]);
           Route::get("returning", [\App\Http\Controllers\DashboardController::class, "returning"]);
       });

       Route::apiResource("users", \App\Http\Controllers\UserController::class);
      Route::apiResource("categories", \App\Http\Controllers\CategoryController::class);
      Route::apiResource("racks", \App\Http\Controllers\RackController::class);
      Route::apiResource("items", \App\Http\Controllers\ItemController::class);
      Route::apiResource("logs", \App\Http\Controllers\LogActivityController::class)->only(["index","show"]);

      Route::apiResource("borrowings", \App\Http\Controllers\BorrowingController::class)->except(["store","update","destroy"]);
      Route::patch("borrowings/{id}/approve", [\App\Http\Controllers\BorrowingController::class, "approve"]);
      Route::patch("borrowings/{id}/reject", [\App\Http\Controllers\BorrowingController::class, "reject"]);

      Route::apiResource("returnings", \App\Http\Controllers\ReturningController::class)->except(["store","update","destroy"]);
      Route::patch("returnings/{id}/approve", [\App\Http\Controllers\ReturningController::class, "approve"]);
      Route::patch("returnings/{id}/reject", [\App\Http\Controllers\ReturningController::class, "reject"]);
   });

   Route::prefix("user")->middleware("role:user")->group(function () {
       Route::apiResource("categories", \App\Http\Controllers\CategoryController::class)->only(["index","show"]);
       Route::apiResource("racks", \App\Http\Controllers\RackController::class)->only(["index","show"]);
       Route::apiResource("items", \App\Http\Controllers\ItemController::class)->only(["index","show"]);
       Route::apiResource("borrows", \App\Http\Controllers\BorrowingController::class)->only(["index","show","store"]);
       Route::apiResource("returns", \App\Http\Controllers\ReturningController::class)->only(["index","show","store"]);
   });

});

Route::fallback(function () {
    return \App\Custom\Formatter::apiResponse(404, "Route not found");
});
