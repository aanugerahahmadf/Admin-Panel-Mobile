<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $testUsers = [];
        $names = ['Andi', 'Sari', 'Budi', 'Dewi', 'Rudi', 'Maya', 'Agus', 'Rina'];
        foreach ($names as $name) {
            $testUsers[] = User::firstOrCreate(
                ['email' => strtolower($name).'@reviewer.test'],
                [
                    'identity_type' => 'ktp',
                    'full_name' => $name,
                    'first_name' => $name,
                    'last_name' => 'User',
                    'username' => strtolower($name).'_reviewer',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'whatsapp' => '628'.str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                ]
            );
        }

        $comments = [
            5 => ['Luar biasa! Sangat memuaskan', 'Kualitas terbaik, recommended!', 'Sempurna untuk acara saya'],
            4 => ['Bagus, sesuai ekspektasi', 'Cukup memuaskan', 'Recommended untuk wedding'],
            3 => ['Cukup baik, ada sedikit kekurangan', 'Standard saja', 'Bisa diterima'],
            2 => ['Kurang memuaskan', 'Tidak sesuai harapan', 'Masih perlu perbaikan'],
            1 => ['Sangat mengecewakan', 'Tidak recommended', 'Buruk sekali'],
        ];

        $packages = Package::all();
        foreach ($packages as $package) {
            $numReviews = random_int(1, 5);
            $selectedUsers = collect($testUsers)->random(min($numReviews, count($testUsers)));
            foreach ($selectedUsers as $user) {
                $rating = random_int(3, 5);
                $comment = $comments[$rating][array_rand($comments[$rating])];
                Review::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'product_id' => null,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);
            }
        }

        $products = Product::all();
        foreach ($products as $product) {
            $numReviews = random_int(1, 5);
            $selectedUsers = collect($testUsers)->random(min($numReviews, count($testUsers)));
            foreach ($selectedUsers as $user) {
                $rating = random_int(3, 5);
                $comment = $comments[$rating][array_rand($comments[$rating])];
                Review::create([
                    'user_id' => $user->id,
                    'package_id' => null,
                    'product_id' => $product->id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);
            }
        }
    }
}
