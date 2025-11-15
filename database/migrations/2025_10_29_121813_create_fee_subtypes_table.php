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
        Schema::create('fee_subtypes', function (Blueprint $table) {
            $table->increments('fee_subtypes_id');
            $table->unsignedInteger('fee_types_id');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('unit', 50);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_subtypes');
    }
};
