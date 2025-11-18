<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_adjustments', function (Blueprint $table) {
            $table->increments('invoice_adjustment_id');

            $table->unsignedInteger('invoice_id');

            // Adjustment type
            $table->enum('adjustment_type', [
                'discount',     
                'waiver',        
                'credit',       
                'penalty',     
                'correction',     
                'extra_service',      
                'back_charge'    
            ]);

            // Tiền (âm = giảm, dương = tăng)
            $table->decimal('amount', 15, 2);

            // Mô tả
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('reason');

            // Liên kết chi tiết hóa đơn / loại phí
            $table->unsignedInteger('invoice_detail_id')->nullable();
            $table->unsignedInteger('fee_types_id')->nullable();

            // Workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->boolean('requires_approval')->default(false);
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Audit
            $table->unsignedInteger('created_by');
            $table->timestamps();

            // Attachment
            $table->string('attachment_url', 500)->nullable();

            // Indexes
            $table->index('invoice_id', 'idx_invoice');
            $table->index('status', 'idx_status');
            $table->index('adjustment_type', 'idx_type');
            $table->index('created_at', 'idx_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_adjustments');
    }
};
