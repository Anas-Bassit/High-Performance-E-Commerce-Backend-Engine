<?php
namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDailySalesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderIds;

    public function __construct($orderIds)
    {
        $this->orderIds = $orderIds;
    }

    public function handle()
    {
        set_time_limit(0);
        $batchRevenue = 0;
        
        $workerPid = getmypid(); 

        $orders = Order::with('items')->whereIn('id', $this->orderIds)->get();

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $batchRevenue += $item->quantity * $item->unit_price;
                usleep(50000); 
            }
        }

        Log::info("Batch processed dynamically", [
            'worker_pid' => $workerPid, 
            'orders_count' => $orders->count(),
            'batch_revenue' => $batchRevenue
        ]);
    }
}