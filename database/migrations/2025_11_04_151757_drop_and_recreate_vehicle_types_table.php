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
        Schema::dropIfExists('vehicle_types');
        
        // Tạo lại bảng với cấu trúc mới
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->increments('vehicle_type_id');
            $table->unsignedInteger('fee_subtypes_id');
            $table->string('code', 30);
            $table->string('name', 50);
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
