<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify ENUM to add 06:30 option
        DB::statement("ALTER TABLE buses MODIFY COLUMN departure_time ENUM('06:00', '06:30', '07:00') DEFAULT '07:00'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM (remove 06:30)
        DB::statement("ALTER TABLE buses MODIFY COLUMN departure_time ENUM('06:00', '07:00') DEFAULT '07:00'");
    }
};
