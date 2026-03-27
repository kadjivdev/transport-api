<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "Bienvenue sur l'ap de transport de kadjiv";
});

Route::get("/debug", function () {
    Log::debug("Les users : ", ["data" => User::all()]);
    return User::all();
});

// // dernière route : toutes les requêtes non‑API reviennent au même point
// Route::get('/{any}', function () {
//     return view('app'); // ou welcome, app.blade.php, etc.
// })->where('any', '.*');