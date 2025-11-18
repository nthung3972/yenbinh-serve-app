<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Thêm các cột bình thường
            $table->decimal('adjustment_total', 15, 2)
                ->default(0)
                ->after('paid_amount');

            $table->integer('adjustment_count')
                ->default(0);

            $table->timestamp('last_adjusted_at')
                ->nullable();

            $table->unsignedInteger('last_adjusted_by')
                ->nullable();
        });

        // Thêm generated column (Laravel không hỗ trợ trực tiếp trong Blueprint)
        DB::statement("
            ALTER TABLE invoices 
            ADD COLUMN final_amount DECIMAL(15,2) 
            GENERATED ALWAYS AS (total_amount + IFNULL(adjustment_total, 0)) STORED
        ");
    }

    public function down(): void
    {
        // Xóa cột generated trước
        DB::statement("ALTER TABLE invoices DROP COLUMN final_amount");

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'adjustment_total',
                'adjustment_count',
                'last_adjusted_at',
                'last_adjusted_by'
            ]);
        });
    }
};
