<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'login_city')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('login_city', 100)->nullable()->after('ip_address');
                $table->string('login_region', 100)->nullable()->after('login_city');
                $table->string('login_country', 100)->nullable()->after('login_region');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login_city', 'login_region', 'login_country']);
        });
    }
};
