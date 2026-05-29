<?php

namespace App\Services\Order;

use App\Jobs\GenerateInvoiceJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Jobs\SendOrderNotificationJob;
use App\Services\Invoice\InvoiceService;

class OrderService
{
    // with lock and trancaction
    public function place(array $validated): Order
    {
        DB::beginTransaction();

        try {
            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->first();
            if (!$product) {
                throw new Exception('Product not found');
            }
            if ($product->stock < $validated['quantity']) {
                throw new Exception('Insufficient stock');
            }
            $totalPrice = $product->price * $validated['quantity'];
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
            ]);
            $product->stock -= $validated['quantity'];
            $product->save();
            DB::commit();
            SendOrderNotificationJob::dispatch($order->id);
            return $order->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    //without lock and trancaction
    public function placewithout(array $validated): Order
    {
        $product = Product::find($validated['product_id']);
        if (!$product) {
            throw new Exception('Product not found');
        }

        if ($product->stock < $validated['quantity']) {
            throw new Exception('Insufficient stock');
        }
        sleep(5);
        $totalPrice = $product->price * $validated['quantity'];
        $order = Order::create([
            'user_id' => $validated['user_id'],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'unit_price' => $product->price,
        ]);
        $product->stock -= $validated['quantity'];
        $product->save();
        SendOrderNotificationJob::dispatch($order->id);
        return $order->load('items');
    }






    private function processOrder(array $validated): Order
    {
        DB::beginTransaction();

        try {

            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new Exception('Product not found');
            }

            if ($product->stock < $validated['quantity']) {
                throw new Exception('Insufficient stock');
            }

            $totalPrice = $product->price * $validated['quantity'];

            $order = Order::create([
                'user_id' => $validated['user_id'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
            ]);

            $product->stock -= $validated['quantity'];
            $product->save();

            DB::commit();

            return $order->load('items');
        } catch (Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }



    public function placeSync(array $validated): Order
    {
        $order = $this->processOrder($validated);

        app(InvoiceService::class)->generate($order);

        sleep(5);

        return $order;
    }


    public function placeAsync(array $validated): Order
    {
        $order = $this->processOrder($validated);

        GenerateInvoiceJob::dispatch($order->id);

        return $order;
    }


    public function placeWithPessimisticLock(array $validated): Order
    {
        return DB::transaction(function () use ($validated) {
            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new Exception('Product not found');
            }

            if ($product->stock < $validated['quantity']) {
                throw new Exception('Insufficient stock');
            }

            $totalPrice = $product->price * $validated['quantity'];

            $order = Order::create([
                'user_id' => $validated['user_id'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
            ]);

            $product->stock -= $validated['quantity'];
            $product->save();

            SendOrderNotificationJob::dispatch($order->id);

            return $order->load('items');
        }, 3);
    }





    public function placeWithTransactionIntegrityTest(array $validated, bool $simulateFailure = false): Order
    {
        return DB::transaction(function () use ($validated, $simulateFailure) {

            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw new Exception('Product not found');
            }

            if ($product->stock < $validated['quantity']) {
                throw new Exception('Insufficient stock');
            }

            $totalPrice = $product->price * $validated['quantity'];

            $order = Order::create([
                'user_id' => $validated['user_id'],
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
            ]);

            $product->stock -= $validated['quantity'];
            $product->save();

            if ($simulateFailure) {
                throw new Exception('Simulated failure after order creation and stock update');
            }

            return $order->load('items');
        }, 3);
    }
}
