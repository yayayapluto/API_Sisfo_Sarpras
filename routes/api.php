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
      Route::apiResources([
          "categories" => \App\Http\Controllers\CategoryController::class,
          "racks" => \App\Http\Controllers\RackController::class,
          "items" => \App\Http\Controllers\ItemController::class,
      ]);

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

Route::prefix("testing")->group(function () {
    Route::get("no-token", function () {
        return \App\Custom\Formatter::apiResponse(200, "This route need no token");
    });

    Route::prefix("need-token")->middleware("need-token")->group(function () {
        Route::get("all", function () {
            return \App\Custom\Formatter::apiResponse(200, "This route for all role");
        });

        Route::middleware("role:admin")->get("admin", function () {
            return \App\Custom\Formatter::apiResponse(200, "This route for admin only");
        });

        Route::middleware("role:user")->get("user", function () {
            return \App\Custom\Formatter::apiResponse(200, "This route for user only");
        });
    });
});

Route::fallback(function () {
    return \App\Custom\Formatter::apiResponse(404, "Route not found");
});
