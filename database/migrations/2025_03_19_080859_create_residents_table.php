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
        Schema::create('residents', function (Blueprint $table) {
            $table->increments('resident_id');
            $table->string('full_name', 100);
            $table->string('id_card_number', 100)->nullable();
            $table->date('date_of_birth');
            $table->string('gender', 20);
            $table->string('phone_number', 20);
            $table->string('email', 100);
            $table->date('move_in_date');
            $table->date('move_out_date')->nullable();
            $table->boolean('resident_type')->default('0')->comment('0 = chủ sở hữu, 1 = người thê, 2 = người thân');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
