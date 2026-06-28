<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistFactory extends Factory
{
    protected $model = Wishlist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'package_id' => null,
            'product_id' => Product::factory(),
        ];
    }

    public function forPackage(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => null,
            'package_id' => \App\Models\Package::factory(),
        ]);
    }
}
