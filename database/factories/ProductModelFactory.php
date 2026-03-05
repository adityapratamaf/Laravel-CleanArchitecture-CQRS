<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;

class ProductModelFactory extends Factory
{
    protected $model = ProductModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->bothify('SKU-###-???')),
            'price' => $this->faker->randomFloat(2, 1000, 999999),
            'stock' => $this->faker->numberBetween(0, 200),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}