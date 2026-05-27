<?php
namespace App\Services\servers;

abstract class Server
{
  public int $activeConnections = 0;

  abstract public function handle(int $userId, int $productId, int $quantity);
}