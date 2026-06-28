<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable()->after('country')
                ->comment('ID provinsi dari indonesia_provinces');
            $table->unsignedBigInteger('city_id')->nullable()->after('province_id')
                ->comment('ID kota/kabupaten dari indonesia_cities');
            $table->unsignedBigInteger('district_id')->nullable()->after('city_id')
                ->comment('ID kecamatan dari indonesia_districts');
            $table->unsignedBigInteger('village_id')->nullable()->after('district_id')
                ->comment('ID kelurahan/desa dari indonesia_villages');
            $table->string('postal_code', 10)->nullable()->after('village_id')
                ->comment('Kode pos');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'province_id',
                'city_id',
                'district_id',
                'village_id',
                'postal_code',
            ]);
        });
    }
};
