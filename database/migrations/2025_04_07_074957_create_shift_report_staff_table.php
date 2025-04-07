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
        Schema::create('shift_report_staff', function (Blueprint $table) {
            $table->increments('shift_report_staff_id');
            $table->unsignedBigInteger('shift_report_id');
            $table->unsignedBigInteger('building_personnel_id');
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->decimal('working_hours', 5, 2)->nullable();
            $table->text('performance_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_report_staff');
    }
};
