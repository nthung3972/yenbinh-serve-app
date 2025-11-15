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
        Schema::create('apartment_transactions', function (Blueprint $table) {
            $table->increments('apartment_transaction_id');
            $table->unsignedInteger('apartment_id');
            $table->enum('transaction_type', ['charge', 'payment', 'adjustment', 'refund'])->index();
            $table->string('reference_type', 50)->nullable(); // invoice, payment, manual
            $table->unsignedInteger('reference_id')->nullable();
            $table->decimal('amount', 15, 2); // +: tăng nợ, -: giảm nợ
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Index tối ưu cho các tình huống truy vấn phổ biến
            // $table->index(['apartment_id', 'created_at'], 'idx_transactions_apartment_created');
            // $table->index(['reference_type', 'reference_id'], 'idx_transactions_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_transactions');
    }
};
