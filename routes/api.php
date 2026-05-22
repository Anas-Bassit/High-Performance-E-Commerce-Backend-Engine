<?php

use App\Http\Controllers\Orders\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Route::post('/orders/place', [OrderController::class, 'place']);

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/orders/place', [OrderController::class, 'place']);
});

Route::get('/simulate', function () {
    $productId = 3;

    for ($i = 0; $i < 3; $i++) {
        \App\Jobs\SimulateOrderJob::dispatch(1, $productId, 1);
        $instance = env('APP_INSTANCE');
        Log::info("Request handled by {$instance}");
    }

    return 'Simulation started';
});


Route::post('/orders/place-sync', [OrderController::class, 'placeSync']);

Route::post('/orders/place-async', [OrderController::class, 'placeAsync']);
