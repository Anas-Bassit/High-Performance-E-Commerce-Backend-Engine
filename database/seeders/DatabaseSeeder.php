<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        Product::create([
            'name' => 'First Product',
            'description' => 'This is a First product.',
            'price' => 19.99,
            'stock' => 100,
        ]);
        Product::create([
            'name' => 'Second Product',
            'description' => 'This is a Second product.',
            'price' => 29.99,
            'stock' => 50,
        ]);
        Product::create([
            'name' => 'Third Product',
            'description' => 'This is a Third product.',
            'price' => 39.99,
            'stock' => 25,
        ]);
        $this->call([
            OrderSeeder::class,
        ]);
    }
}
