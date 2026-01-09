<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => Customer::factory(),
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'total_amount' => $this->faker->numberBetween(10000, 500000) / 100,
            'status' => $this->faker->randomElement(['pending', 'paid', 'cancelled']),
        ];
    }
}
