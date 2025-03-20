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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id('invoice_detail_id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('service_name', 100); // Tên dịch vụ (VD: Phí quản lý, phí gửi xe)
            $table->decimal('amount', 15, 2); // Số tiền
            $table->string('description', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
