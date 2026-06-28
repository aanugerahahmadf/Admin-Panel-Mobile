<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'package_id' => null,
            'quantity' => fake()->numberBetween(1, 5),
            'meta' => [
                'notes' => fake()->optional()->sentence(),
            ],
        ];
    }

    public function withPackage(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => null,
            'package_id' => \App\Models\Package::factory(),
        ]);
    }
}
