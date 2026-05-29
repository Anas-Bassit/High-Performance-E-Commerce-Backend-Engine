<?php

use App\Http\Controllers\Orders\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/orders/place', [OrderController::class, 'place']);
// Route::post('/api/placewithout', [OrderController::class , 'placewithout']);
// Route::middleware('throttle:5,1')->group(function () {
//     Route::post('/orders/place', [OrderController::class, 'place']);
// });
Route::post('/orders/place-with-lock', [OrderController::class, 'placewithout']);
Route::post('/orders/place-without-lock', [OrderController::class, 'PlaceWithLock']);
Route::post('/orders/transaction-test', [OrderController::class, 'placeTransactionTest']);
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
Route::get('/health', function () {
    $instance = getenv('APP_INSTANCE');

    return response()->json([
        'status' => 'healthy',
        'instance' => $instance ?: gethostname(),
        'hostname' => gethostname(),
        'time' => now()->toDateTimeString(),
    ]);
});

Route::get('/server-info', function () {
    $instance = getenv('APP_INSTANCE');

    return response()->json([
        'instance' => $instance ?: gethostname(),
        'hostname' => gethostname(),
        'pid' => getmypid(),
        'time' => now()->toDateTimeString(),
    ]);
});

Route::get('/debug-db', function () {
    return response()->json([
        'hostname' => gethostname(),
        'app_instance' => getenv('APP_INSTANCE') ?: 'missing',
        'config_driver' => config('database.default'),
        'real_driver' => DB::connection()->getDriverName(),
        'database_name' => DB::connection()->getDatabaseName(),
        'db_host' => config('database.connections.mysql.host'),
    ]);
});