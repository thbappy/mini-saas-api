<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->word() . ' ' . $this->faker->word(),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-?????-###')),
            'price' => $this->faker->numberBetween(1000, 100000) / 100,
            'stock_quantity' => $this->faker->numberBetween(0, 500),
            'low_stock_threshold' => $this->faker->numberBetween(5, 50),
            'description' => $this->faker->text(200),
        ];
    }
}
