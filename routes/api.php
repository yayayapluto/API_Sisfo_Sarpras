<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->group(function () {
   Route::post("login", [\App\Http\Controllers\AuthController::class, "login"]);
   Route::get("logout", [\App\Http\Controllers\AuthController::class, "logout"])->middleware("need-token");
});

Route::middleware("need-token")->group(function () {
   Route::prefix("admin")->middleware("role:admin")->group(function () {
      Route::apiResources([
          "categories" => \App\Http\Controllers\CategoryController::class,
          "racks" => \App\Http\Controllers\RackController::class
      ]);
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
