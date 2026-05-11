<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessDailySales extends Command
{
    protected $signature = 'sales:process';

    protected $description = 'Process sales using batch chunks';

    public function handle()
    {
        $totalRevenue = 0;

        Order::with('items')
            ->orderBy('id')
            ->chunkById(100, function ($orders) use (&$totalRevenue) {

                Log::info("Processing batch of " . $orders->count() . " orders");

                $batchRevenue = 0;

                foreach ($orders as $order) {
                    foreach ($order->items as $item) {
                        $batchRevenue += $item->quantity * $item->unit_price;

                        usleep(50000);
                    }
                }

                $totalRevenue += $batchRevenue;

                Log::info("Batch processed: " . $batchRevenue);
            });

        Log::info("All batches processed successfully", [
            'total_revenue' => $totalRevenue
        ]);
    }
}
