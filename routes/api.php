<?php

use App\Http\Controllers\Orders\OrderController;
use App\Services\LoadBalancer\LoadBalancerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/orders/place', [OrderController::class, 'place']);
// Route::post('/api/placewithout', [OrderController::class , 'placewithout']);
// Route::middleware('throttle:5,1')->group(function () {
//     Route::post('/orders/place', [OrderController::class, 'place']);
// });

Route::get('/simulate', function () {
    $productId = 1;

    for ($i = 0; $i < 20; $i++) {
        \App\Jobs\SimulateOrderJob::dispatch(1, $productId, 1);
    }

    return 'Simulation started';
});


Route::post('/orders/place-sync', [OrderController::class, 'placeSync']);

Route::post('/orders/place-async', [OrderController::class, 'placeAsync']);


Route::get('/test-job', function () {
    dispatch(new \App\Jobs\DispatchDailySalesJobs);
    return 'Job Dispatched';
});

Route::post('/load-balancing', function () {
    $userId = 1;
    $productId = 1;
    $quantity = 1;
    $loadBalancerService = new LoadBalancerService();

    for ($i = 0; $i < 10; $i++) {
        $loadBalancerService->roundRobin($userId, $productId, $quantity);
    }
    return response()->json([
        'message' => 'Order created successfully'
    ], 201);
});
