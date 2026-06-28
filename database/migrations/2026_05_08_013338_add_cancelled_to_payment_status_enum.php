<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not support MODIFY COLUMN — skip on SQLite (used in testing)
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_status` ENUM('unpaid','pending','partial','paid','failed','refunded','cancelled') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Revert rows with 'cancelled' back to 'unpaid' before removing the value
        DB::statement("UPDATE `orders` SET `payment_status` = 'unpaid' WHERE `payment_status` = 'cancelled'");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `payment_status` ENUM('unpaid','pending','partial','paid','failed','refunded') NOT NULL DEFAULT 'unpaid'");
    }
};
