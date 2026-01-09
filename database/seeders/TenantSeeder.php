<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create 2 tenants with sample data
        $tenants = Tenant::factory(2)->create();

        foreach ($tenants as $tenant) {
            // Create owner user
            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $tenant->name . ' Owner',
                'email' => 'owner-' . $tenant->slug . '@example.com',
                'password' => Hash::make('password123'),
                'role' => 'owner',
            ]);

            // Create staff users
            User::factory(2)
                ->for($tenant)
                ->create([
                    'role' => 'staff',
                ]);

            // Create products
            $products = Product::factory(15)
                ->create([
                    'tenant_id' => $tenant->id,
                ])
                ->each(function ($product) {
                    $product->update([
                        'stock_quantity' => rand(5, 200),
                    ]);
                });

            // Create customers
            $customers = Customer::factory(10)
                ->create([
                    'tenant_id' => $tenant->id,
                ]);

            // Create orders with items
            foreach ($customers as $customer) {
                $numOrders = rand(1, 5);
                
                for ($i = 0; $i < $numOrders; $i++) {
                    $order = Order::create([
                        'tenant_id' => $tenant->id,
                        'customer_id' => $customer->id,
                        'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                        'total_amount' => 0,
                        'status' => $i % 3 === 0 ? 'paid' : ($i % 3 === 1 ? 'pending' : 'cancelled'),
                    ]);

                    // Add order items
                    $total = 0;
                    $selectedProducts = $products->random(rand(1, 3));
                    
                    foreach ($selectedProducts as $product) {
                        $quantity = rand(1, 10);
                        $subtotal = $product->price * $quantity;
                        $total += $subtotal;
                        
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'unit_price' => $product->price,
                            'subtotal' => $subtotal,
                        ]);
                    }

                    $order->update(['total_amount' => $total]);
                }
            }
        }
    }
}
