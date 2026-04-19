<?php

use App\Http\Controllers\Orders\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/orders/place', [OrderController::class, 'place']);


Route::get('/simulate', function () {
    $productId = 3;

    for ($i = 0; $i < 20; $i++) {
        \App\Jobs\SimulateOrderJob::dispatch(1, $productId, 1);
    }

    return 'Simulation started';
});
