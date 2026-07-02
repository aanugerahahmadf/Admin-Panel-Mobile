<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('religion')->nullable()->after('gender');
            $table->string('marital_status')->nullable()->after('religion');
            $table->string('mother_name')->nullable()->after('marital_status');
            $table->string('occupation')->nullable()->after('mother_name');
            $table->string('income_range')->nullable()->after('occupation');
            $table->string('source_of_funds')->nullable()->after('income_range');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['religion', 'marital_status', 'mother_name', 'occupation', 'income_range', 'source_of_funds']);
        });
    }
};
