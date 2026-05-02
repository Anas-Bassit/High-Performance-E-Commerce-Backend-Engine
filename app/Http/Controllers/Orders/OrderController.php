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

}
