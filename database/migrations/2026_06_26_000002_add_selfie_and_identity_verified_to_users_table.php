<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('selfie_photo')->nullable()->after('ktp_photo')
                ->comment('Path foto selfie untuk verifikasi wajah');
            $table->timestamp('identity_verified_at')->nullable()->after('selfie_photo')
                ->comment('Waktu verifikasi identitas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['selfie_photo', 'identity_verified_at']);
        });
    }
};
