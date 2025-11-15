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
        Schema::create('utility_readings', function (Blueprint $table) {
            $table->increments('utility_reading_id');
            $table->unsignedInteger('apartment_id');
            $table->unsignedInteger('building_id');
            $table->string('utility_type');
            $table->string('period', 7); // dạng YYYY-MM
            $table->decimal('previous_reading', 10, 2)->nullable();
            $table->decimal('current_reading', 10, 2)->nullable();
            $table->decimal('consumption', 10, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('reading_date')->nullable();
            $table->string('reader_name', 100)->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'billed'])->default('pending');
            $table->timestamps();

            // Mỗi căn hộ chỉ có 1 bản ghi / loại tiện ích / kỳ
            $table->unique(['apartment_id', 'utility_type', 'period'], 'unique_apartment_utility_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
    }
};
