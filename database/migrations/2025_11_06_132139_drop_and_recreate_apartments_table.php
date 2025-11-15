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
        Schema::dropIfExists('apartments');

        Schema::create('apartments', function (Blueprint $table) {
            $table->increments('apartment_id');
            $table->unsignedInteger('building_id');
            $table->unsignedInteger('updated_by');
            $table->string('apartment_number', 20);
            $table->integer('floor_number');
            $table->float('area');
            $table->string('apartment_type');
            $table->text('notes')->nullable();
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
