<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->after('whatsapp')
                ->comment('Nomor Induk Kependudukan (KTP)');
            $table->string('ktp_photo')->nullable()->after('nik')
                ->comment('Path foto KTP');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik', 'ktp_photo']);
        });
    }
};
