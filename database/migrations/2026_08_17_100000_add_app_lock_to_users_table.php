<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('app_lock_fingerprint_enabled')->default(false);
            $table->boolean('app_lock_face_enabled')->default(false);
            $table->boolean('app_lock_pin_enabled')->default(false);
            $table->string('app_lock_pin_hash')->nullable();
            $table->boolean('app_lock_face_enrolled')->default(false);
            $table->string('app_lock_face_reference')->nullable();
            $table->timestamp('app_lock_face_enrolled_at')->nullable();
            $table->timestamp('app_lock_last_unlock_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'app_lock_fingerprint_enabled',
                'app_lock_face_enabled',
                'app_lock_pin_enabled',
                'app_lock_pin_hash',
                'app_lock_face_enrolled',
                'app_lock_face_reference',
                'app_lock_face_enrolled_at',
                'app_lock_last_unlock_at',
            ]);
        });
    }
};
