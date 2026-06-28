<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('packages', 'wedding_organizer_id') && !Schema::hasColumn('packages', 'wedding_flowers_decorasi_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->renameColumn('wedding_organizer_id', 'wedding_flowers_decorasi_id');
            });
        }

        if (Schema::hasColumn('products', 'wedding_organizer_id') && !Schema::hasColumn('products', 'wedding_flowers_decorasi_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('wedding_organizer_id', 'wedding_flowers_decorasi_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('packages', 'wedding_flowers_decorasi_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->renameColumn('wedding_flowers_decorasi_id', 'wedding_organizer_id');
            });
        }

        if (Schema::hasColumn('products', 'wedding_flowers_decorasi_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('wedding_flowers_decorasi_id', 'wedding_organizer_id');
            });
        }
    }
};
