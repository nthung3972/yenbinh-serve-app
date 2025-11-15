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
        Schema::dropIfExists('invoices');

        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('invoice_id');
            $table->string('invoice_number')->unique()->comment('Mã hóa đơn');
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('apartment_id');
            $table->string('period')->comment('Kỳ thu phí, ví dụ 2025-10');
            $table->date('issue_date')->comment('Ngày phát hành hóa đơn');
            $table->date('due_date')->comment('Hạn thanh toán');
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending')->comment('Trạng thái hóa đơn');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Tổng tiền hóa đơn');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Tổng tiền đã thanh toán');
            $table->decimal('opening_balance', 15, 2)->default(0)->comment('Tổng Nợ đầu kỳ');
            $table->decimal('closing_balance', 15, 2)->default(0)->comment('Tổng Nợ cuối kì');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
