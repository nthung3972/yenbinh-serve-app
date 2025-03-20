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
            $table->string('relationship', 100);
            $table->date('move_in_date');
            $table->date('move_out_date');
            $table->boolean('is_primary_resident')->default('0')->comment('0 = không phải chủ sở hữu, 1 = chủ sở hữu');
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
