<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepenseLocationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LocationTypeController;
use App\Http\Controllers\ReglementLocationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix("/v1")->group(function () {
    require __DIR__ . '/auth.php';

    // Refresh route
    Route::post('/refresh', [AuthenticatedSessionController::class, 'refresh'])
        ->name('refresh');

    // Protected routes
    Route::middleware(['jwt.from.cookie'])->group(function () {
        // Logout route
        Route::post('/logout', [AuthenticatedSessionController::class, 'logout']) // old destroy
            ->name('logout');

        Route::get("/permissions", [RoleController::class, "getPermissions"])->name("permissions");
        Route::post("/affect-role", [RoleController::class, "affectRole"])->name("affect-role");

        Route::apiResource("users", UserController::class)->except(["create", "edit"]);
        Route::apiResource("roles", RoleController::class)->except(["create", "edit"]);
        Route::apiResource("clients", ClientController::class)->except(["create", "edit"]);
        Route::apiResource("camions", CamionController::class)->except(["create", "edit"]);
        Route::apiResource("locations", LocationController::class)->except(["create", "edit"]);
        Route::apiResource("reglements", ReglementLocationController::class)->except(["create", "edit"]);
        Route::apiResource("depenses", DepenseLocationController::class)->except(["create", "edit"]);
        Route::apiResource("location-types", LocationTypeController::class)->except(["create", "edit"]);

        // validation's routes
        Route::post("/locations/validate/{location}", [LocationController::class, "validate"])->name("location.validate");
        Route::post("/depenses/validate/{depense}", [DepenseLocationController::class, "validate"]);
        Route::post("/reglements/validate/{reglement}", [ReglementLocationController::class, "validate"]);
    });
});
