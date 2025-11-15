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
        Schema::create('utility_import_logs', function (Blueprint $table) {
            $table->increments('utility_import_log_id');
            $table->string('period', 7);
            $table->string('utility_type');
            $table->string('file_name', 255)->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('error_rows')->default(0);
            $table->json('errors')->nullable();
            $table->unsignedInteger('imported_by');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utility_import_logs');
    }
};
