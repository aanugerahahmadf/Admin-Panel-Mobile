<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_options', function (Blueprint $table) {
            $table->id();
            $table->string('type');            // gender, religion, marital_status, etc.
            $table->string('key');             // male, female, islam, christian, etc.
            $table->json('label');             // {"en": "Male", "id": "Laki-laki"}
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->unique(['type', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_options');
    }
};
