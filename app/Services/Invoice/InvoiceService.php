<?php
namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\Order;

class InvoiceService
{
    public function generate(Order $order): Invoice
    {
        return Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . time() . '-' . $order->id,
            'total_amount' => $order->total_price,
            'status' => 'generated',
        ]);
    }
}
