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
        Schema::create('apartments', function (Blueprint $table) {
            $table->increments('apartment_id');
            $table->integer('building_id');
            $table->string('apartment_number', 10);
            $table->integer('floor_number');
            $table->float('area');
            $table->boolean('status')->default('0')->comment('0 = để trống, 1 = đang sử dụng, cho thuê');
            $table->string('ownership_type', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
