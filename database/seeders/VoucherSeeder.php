<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Voucher;
use App\Traits\TranslatesContent;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    use TranslatesContent;

    public function run(): void
    {
        // ── 1. VOUCHER PUBLIC (is_global = true) ──────────────────────────

        $publicVouchers = [
            [
                'code' => 'WELCOME10',
                'description' => 'Diskon 10% untuk semua pengguna baru',
                'discount_amount' => 10,
                'discount_type' => 'percentage',
                'min_purchase' => 500_000,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
                'is_global' => true,
                'max_uses' => 500,
                'uses_count' => 0,
            ],
            [
                'code' => 'HEMAT50K',
                'description' => 'Potongan Rp 50.000 untuk semua paket',
                'discount_amount' => 50_000,
                'discount_type' => 'fixed',
                'min_purchase' => 1_000_000,
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
                'is_global' => true,
                'max_uses' => 200,
                'uses_count' => 0,
            ],
            [
                'code' => 'PROMO2026',
                'description' => 'Promo spesial tahun 2026 — diskon 15%',
                'discount_amount' => 15,
                'discount_type' => 'percentage',
                'min_purchase' => 2_000_000,
                'expires_at' => now()->endOfYear(),
                'is_active' => true,
                'is_global' => true,
                'max_uses' => 100,
                'uses_count' => 0,
            ],
            [
                'code' => 'GRATIS100K',
                'description' => 'Potongan Rp 100.000 tanpa minimum pembelian',
                'discount_amount' => 100_000,
                'discount_type' => 'fixed',
                'min_purchase' => 0,
                'expires_at' => now()->addMonth(),
                'is_active' => true,
                'is_global' => true,
                'max_uses' => 50,
                'uses_count' => 0,
            ],
            [
                'code' => 'FLASHSALE',
                'description' => 'Flash sale — diskon 20% (terbatas!)',
                'discount_amount' => 20,
                'discount_type' => 'percentage',
                'min_purchase' => 1_500_000,
                'expires_at' => now()->addWeeks(2),
                'is_active' => true,
                'is_global' => true,
                'max_uses' => 30,
                'uses_count' => 0,
            ],
        ];

        foreach ($publicVouchers as $data) {
            $descTranslations = $this->translateToAllLocales($data['description']);
            Voucher::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['description_translations' => $descTranslations])
            );
        }

        $this->command->info('✅ '.count($publicVouchers).' voucher PUBLIC berhasil dibuat.');

        // ── 2. VOUCHER PER-USER (is_global = false) ───────────────────────

        $perUserVouchers = [
            [
                'code' => 'VIP-GOLD-25',
                'description' => 'Voucher eksklusif member Gold — diskon 25%',
                'discount_amount' => 25,
                'discount_type' => 'percentage',
                'min_purchase' => 3_000_000,
                'expires_at' => now()->addMonths(12),
                'is_active' => true,
                'is_global' => false,
                'max_uses' => null,
                'uses_count' => 0,
            ],
            [
                'code' => 'LOYAL-200K',
                'description' => 'Hadiah loyalitas — potongan Rp 200.000',
                'discount_amount' => 200_000,
                'discount_type' => 'fixed',
                'min_purchase' => 2_500_000,
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
                'is_global' => false,
                'max_uses' => null,
                'uses_count' => 0,
            ],
            [
                'code' => 'BIRTHDAY-FREE',
                'description' => 'Voucher ulang tahun — diskon 30%',
                'discount_amount' => 30,
                'discount_type' => 'percentage',
                'min_purchase' => 1_000_000,
                'expires_at' => now()->addMonths(1),
                'is_active' => true,
                'is_global' => false,
                'max_uses' => null,
                'uses_count' => 0,
            ],
            [
                'code' => 'REFERRAL-75K',
                'description' => 'Bonus referral — potongan Rp 75.000',
                'discount_amount' => 75_000,
                'discount_type' => 'fixed',
                'min_purchase' => 500_000,
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
                'is_global' => false,
                'max_uses' => null,
                'uses_count' => 0,
            ],
            [
                'code' => 'EARLY-BIRD-15',
                'description' => 'Early bird booking — diskon 15%',
                'discount_amount' => 15,
                'discount_type' => 'percentage',
                'min_purchase' => 5_000_000,
                'expires_at' => now()->addMonths(4),
                'is_active' => true,
                'is_global' => false,
                'max_uses' => null,
                'uses_count' => 0,
            ],
        ];

        $createdPerUser = [];
        foreach ($perUserVouchers as $data) {
            $descTranslations = $this->translateToAllLocales($data['description']);
            $createdPerUser[] = Voucher::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['description_translations' => $descTranslations])
            );
        }

        $this->command->info('✅ '.count($perUserVouchers).' voucher PER-USER berhasil dibuat.');

        // ── 3. ASSIGN VOUCHER PER-USER KE USER ────────────────────────────

        $customers = User::role('customer')->get();

        if ($customers->isEmpty()) {
            $customers = User::whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            })->get();
        }

        if ($customers->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada user customer ditemukan. Voucher per-user tidak di-assign.');
            return;
        }

        $assignedCount = 0;
        foreach ($customers as $user) {
            foreach ($createdPerUser as $voucher) {
                $voucher->assignToUser($user->id);
                $assignedCount++;
            }
        }

        $this->command->info("✅ {$assignedCount} assignment voucher per-user ke {$customers->count()} user berhasil.");
    }
}
