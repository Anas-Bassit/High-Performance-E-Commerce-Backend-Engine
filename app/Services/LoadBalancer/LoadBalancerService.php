<?php

namespace App\Services\LoadBalancer;

use App\Services\servers\Server1;
use App\Services\servers\Server2;
use App\Services\servers\Server3;

class LoadBalancerService
{
  private array $servers;
  private int $currentIndex = 0;
  public function __construct()
  {
    $this->servers = [
      new Server1(),
      new Server2(),
      new Server3()
    ];
  }

  public function roundRobin(int $userId, int $productId, int $quantity)
  {

    $server = $this->servers[$this->currentIndex];

    $server->handle($userId, $productId, $quantity);
    if ($this->currentIndex == 2) {
      $this->currentIndex = 0;
    } else {
      $this->currentIndex++;
    }
  }
}
