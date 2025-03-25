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
        Schema::create('apartment_resident', function (Blueprint $table) {
            $table->integer('apartment_id');
            $table->integer('resident_id');
            $table->boolean('role_in_apartment')->default('0')->comment('0 = chủ hộ, 1 = người thuê chính, 2 = người thân');
            $table->date('registration_date');
            $table->boolean('registration_status')->default('0')->comment('0 = đang cư trú, 1 = đã rời đi');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_resident');
    }
};
