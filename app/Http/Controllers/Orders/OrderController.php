<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function place(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = $orderService->place($validated);

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    public function placewithout(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = $orderService->placewithout($validated);

            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    public function placeSync(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {

            $order = $orderService->placeSync($validated);

            return response()->json([
                'message' => 'Sync order created successfully',
                'data' => $order,
            ]);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }



    public function placeAsync(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {

            $order = $orderService->placeAsync($validated);

            return response()->json([
                'message' => 'Async order created successfully',
                'data' => $order,
            ]);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    public function placeDistributedLock(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = $orderService->placeWithDistributedLock($validated);

            return response()->json([
                'message' => 'Order created successfully with distributed lock',
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function placeTransactionTest(Request $request, OrderService $orderService)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'simulate_failure' => ['nullable', 'boolean'],
        ]);

        try {
            $order = $orderService->placeWithTransactionIntegrityTest(
                $validated,
                $request->boolean('simulate_failure')
            );

            return response()->json([
                'message' => 'Order created successfully using transaction integrity',
                'data' => $order,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
