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
        Schema::create('building_fees', function (Blueprint $table) {
            $table->increments('building_fee_id');
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('fee_types_id');
            $table->unsignedInteger('fee_subtypes_id');
            $table->decimal('price', 12, 2);
            $table->string('unit', 50); 
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_fees');
    }
};
