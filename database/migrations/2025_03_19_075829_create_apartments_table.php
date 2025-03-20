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
            $table->string('apartment_number', 20);
            $table->integer('floor_number');
            $table->float('area');
            $table->boolean('status')->default('0')->comment('0 = đang sử dụng, 1 = để trống');
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
