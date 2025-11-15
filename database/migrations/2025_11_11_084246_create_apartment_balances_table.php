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
        Schema::create('apartment_balances', function (Blueprint $table) {
            $table->increments('apartment_balance_id');
            $table->unsignedInteger('apartment_id');
            $table->unsignedInteger('building_id');
            $table->decimal('current_balance', 15, 2)->default(0)->comment('Âm = nợ, Dương = dư');
            $table->decimal('total_charged', 15, 2)->default(0)->comment('Tổng đã tính phí');
            $table->decimal('total_paid', 15, 2)->default(0)->comment('Tổng đã thanh toán');
            $table->date('last_charge_date')->nullable();
            $table->date('last_payment_date')->nullable();
            $table->timestamps();

            // Index tối ưu cho tra cứu công nợ
            // $table->index('apartment_id', 'idx_balance_apartment');
            // $table->index('current_balance', 'idx_balance_current_balance');
            // $table->index('building_id', 'idx_balance_building');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_balances');
    }
};
