<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('banks')->updateOrInsert(
            ['code' => 'bri'],
            [
                'name'             => 'Bank BRI',
                'type'             => 'bank_transfer',
                'account_number'   => '421201032041536',
                'account_holder'   => 'Anugerah Ahmad Fachrurochim',
                'logo'             => null,
                'qris_payload'     => null,
                'qris_image'       => null,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );
    }
}