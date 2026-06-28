<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('nik')
                ->comment('Tempat lahir sesuai KTP');
            $table->date('birth_date')->nullable()->after('birth_place')
                ->comment('Tanggal lahir sesuai KTP');
            $table->string('country')->nullable()->after('address')
                ->comment('Negara sesuai KTP');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birth_place', 'birth_date', 'country']);
        });
    }
};
