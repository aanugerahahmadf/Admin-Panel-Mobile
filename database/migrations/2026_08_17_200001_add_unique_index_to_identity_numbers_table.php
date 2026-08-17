<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('ktp_number');
            $table->unique('passport_number');
            $table->unique('sim_number');
            $table->unique('npwp_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['ktp_number']);
            $table->dropUnique(['passport_number']);
            $table->dropUnique(['sim_number']);
            $table->dropUnique(['npwp_number']);
        });
    }
};
