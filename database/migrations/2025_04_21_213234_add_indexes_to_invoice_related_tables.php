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
        // Bảng invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('apartment_id');
            $table->index('building_id');
            $table->index('status');
            $table->index('invoice_date');
            $table->index('due_date');
            $table->index('remaining_balance');
        });

        // Bảng apartments
        Schema::table('apartments', function (Blueprint $table) {
            $table->index('building_id');
            $table->index('apartment_number');
        });

         // Bảng invoice_details
         Schema::table('invoice_details', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('fee_type_id');
        });

         // Bảng payments
         Schema::table('payments', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['apartment_id']);
            $table->dropIndex(['building_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['remaining_balance']);
        });

        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex(['building_id']);
            $table->dropIndex(['apartment_number']);
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['fee_type_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['payment_date']);
        });
    }
};
