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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->increments('vehicle_id');
            $table->string('license_plate', 30)->unique();
            $table->enum('vehicle_type', ['car', 'motorbike', 'bicycle']);
            $table->string('parking_slot', 20)->nullable();
            $table->boolean('status')->default('0')->comment('0 = active, 1 = inactive');
            $table->integer('resident_id');
            $table->integer('building_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
