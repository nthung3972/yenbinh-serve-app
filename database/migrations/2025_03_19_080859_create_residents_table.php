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
            $table->date('date_of_birth');
            $table->string('phone_number', 15);
            $table->string('email', 15);
            $table->date('registration_date');
            $table->boolean('is_owner')->default('0')->comment('0 = không phải chủ sở hữu, 1 = chủ sở hữu');
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
