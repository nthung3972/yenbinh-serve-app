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
        Schema::table('residents', function (Blueprint $table) {
            $table->string('full_name', 100)->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->string('phone_number', 20)->nullable()->change();
            $table->string('email', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->string('full_name', 100)->change();
            $table->date('date_of_birth')->change();
            $table->string('phone_number', 20)->change();
            $table->string('email', 100)->change();
        });
    }
};
