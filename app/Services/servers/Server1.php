<?php
namespace App\Services\servers;

use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;

class Server1 extends Server
{

  public function handle(int $userId, int $productId, int $quantity)
  {
    $this->activeConnections++;
    $orderService = new OrderService();
    $orderService->place([
      'user_id' => $userId,
      'product_id' => $productId,
      'quantity' => $quantity
    ]);

    $this->activeConnections--;

    Log::alert("Server 1 handled request for user {$userId} with product {$productId} and quantity {$quantity}");
    return [
      'server' => 'SERVER_1'
    ];
  }
}
