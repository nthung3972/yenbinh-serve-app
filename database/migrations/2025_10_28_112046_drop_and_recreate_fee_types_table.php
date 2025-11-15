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
        Schema::dropIfExists('fee_types');
        
        // Tạo lại bảng với cấu trúc mới
        Schema::create('fee_types', function (Blueprint $table) {
            $table->increments('fee_types_id');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};
