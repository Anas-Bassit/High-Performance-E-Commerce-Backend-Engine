<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5000; $i++) {

            $orderId = DB::table('orders')->insertGetId([

                'user_id' => DB::table('users')
                    ->inRandomOrder()
                    ->value('id'),
                'total_price' => rand(100, 1000),

                'status' => 'completed',

                'created_at' => now(),

                'updated_at' => now(),
            ]);

            $itemsCount = rand(1, 5);

            for ($j = 1; $j <= $itemsCount; $j++) {

                DB::table('order_items')->insert([

                    'order_id' => $orderId,

                    'product_id' => rand(1, 3),

                    'quantity' => rand(1, 5),

                    'unit_price' => rand(50, 500),

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);
            }
        }
    }
}
