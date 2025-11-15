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
        // Xóa bảng cũ nếu tồn tại
        Schema::dropIfExists('vehicles');
        
        // Tạo lại bảng với cấu trúc mới
        Schema::create('vehicles', function (Blueprint $table) {
            $table->increments('vehicle_id');
            $table->unsignedInteger('vehicle_type_id');
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('resident_id');
            $table->string('apartment_number', 10);
            $table->string('license_plate', 30)->nullable();
            $table->string('parking_slot', 30)->nullable();
            $table->string('vehicle_company', 100)->nullable();
            $table->string('vehicle_model', 100)->nullable();
            $table->string('vehicle_color', 100)->nullable();
            $table->date('inactive_date')->nullable();
            $table->string('status', 30);
            $table->unsignedInteger('updated_by');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
