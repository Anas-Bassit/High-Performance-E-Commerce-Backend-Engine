<?php

namespace App\Jobs;

use App\Services\Order\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SimulateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public int $productId;
    public int $quantity;

    public function __construct($userId, $productId, $quantity)
    {
        $this->userId = $userId;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function handle(OrderService $orderService)
    {
        try {
            $orderService->placewithout([
                'user_id' => $this->userId,
                'product_id' => $this->productId,
                'quantity' => $this->quantity,
            ]);
        } catch (\Throwable $e) {
            Log::info('Order failed: ' . $e->getMessage());
        }
    }
}