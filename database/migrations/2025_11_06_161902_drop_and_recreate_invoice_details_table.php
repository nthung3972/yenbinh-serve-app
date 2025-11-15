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
        Schema::dropIfExists('invoice_details');

        Schema::create('invoice_details', function (Blueprint $table) {
            $table->increments('invoice_detail_id');
            $table->unsignedInteger('invoice_id'); 
            $table->unsignedInteger('fee_types_id');
            $table->unsignedInteger('fee_subtypes_id');
            $table->unsignedInteger('building_fee_id');
            $table->unsignedInteger('reference_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->string('description')->nullable()->comment('Mô tả chi tiết dòng phí');
            $table->decimal('quantity', 10, 2)->default(1)->comment('Số lượng (ví dụ m2, xe, kWh...)');
            $table->decimal('unit_price', 15, 2)->default(0)->comment('Đơn giá');
            $table->decimal('amount', 15, 2)->default(0)->comment('Thành tiền');
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
