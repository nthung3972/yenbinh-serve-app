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
        Schema::create('debt_logs', function (Blueprint $table) {
            $table->increments('debt_log_id');
            $table->integer('building_id');
            $table->integer('apartment_id');
            $table->date('log_date');
            $table->decimal('total_debt',10,2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_logs');
    }
};
