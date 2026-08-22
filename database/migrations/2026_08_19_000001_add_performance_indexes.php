<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('virtual_account_no');
            $table->index('status');
        });

        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->index(['order_id', 'user_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });

        Schema::table('fm_messages', function (Blueprint $table) {
            $table->index(['inbox_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['virtual_account_no']);
            $table->dropIndex(['status']);
        });

        Schema::table('user_vouchers', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'user_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('fm_messages', function (Blueprint $table) {
            $table->dropIndex(['inbox_id', 'created_at']);
        });
    }
};
