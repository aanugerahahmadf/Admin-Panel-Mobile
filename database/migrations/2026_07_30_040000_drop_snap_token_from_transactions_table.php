<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('transactions', 'snap_token')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('snap_token');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('transactions', 'snap_token')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('snap_token')->nullable()->after('payment_method');
            });
        }
    }
};
