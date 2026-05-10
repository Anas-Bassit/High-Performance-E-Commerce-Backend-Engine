<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Invoice\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceJob implements ShouldQueue
{
    use Queueable;

    public int $orderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceService $invoiceService): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            Log::warning("Order not found for invoice generation: {$this->orderId}");
            return;
        }

        sleep(5);

        $invoice = $invoiceService->generate($order);

        Log::info('Invoice created: ' . $invoice->invoice_number . ' for order #' . $order->id);
    }
}
