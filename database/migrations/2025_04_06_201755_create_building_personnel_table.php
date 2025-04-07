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
        Schema::create('building_personnel', function (Blueprint $table) {
            $table->increments('building_personnel_id');
            $table->unsignedBigInteger('building_id');
            $table->string('personnel_name', 50);
            $table->string('personnel_phone', 15);
            $table->string('personnel_address', 100);
            $table->enum('position', ['receptionist', 'technical', 'cleaner', 'accountant', 'security']);
            $table->boolean('status')->default('0')->comment('0 = active, 1 = inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_personnel');
    }
};
