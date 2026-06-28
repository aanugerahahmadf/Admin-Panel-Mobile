<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('province_name', 255)->nullable()->after('village_id')
                ->comment('Nama provinsi/state (untuk semua negara)');
            $table->string('city_name', 255)->nullable()->after('province_name')
                ->comment('Nama kota/kabupaten/city (untuk semua negara)');
            $table->string('district_name', 255)->nullable()->after('city_name')
                ->comment('Nama kecamatan/district (untuk semua negara)');
            $table->string('village_name', 255)->nullable()->after('district_name')
                ->comment('Nama kelurahan/desa/village (untuk semua negara)');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['province_name', 'city_name', 'district_name', 'village_name']);
        });
    }
};
