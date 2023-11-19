<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Truncate tables before seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Customer::truncate();
        Product::truncate();
        Order::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed the database
        $this->call([
            CustomerSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
