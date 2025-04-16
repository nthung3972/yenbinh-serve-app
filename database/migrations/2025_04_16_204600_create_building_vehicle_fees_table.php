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
        Schema::create('building_vehicle_fees', function (Blueprint $table) {
            $table->increments('building_vehicle_fee_id');
            $table->integer('building_id');
            $table->integer('vehicle_type_id');
            $table->decimal('parking_fee',10,2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_vehicle_fees');
    }
};
