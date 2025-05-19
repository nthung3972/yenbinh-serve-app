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
        Schema::table('building_personnel', function (Blueprint $table) {
            $table->enum('position', ['receptionist', 'technical', 'cleaner', 'accountant', 'security', 'supervisor', 'assistant_manager', 'manager'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('building_personnel', function (Blueprint $table) {
            $table->enum('position', ['receptionist', 'technical', 'cleaner', 'accountant', 'security']);
        });
    }
};
