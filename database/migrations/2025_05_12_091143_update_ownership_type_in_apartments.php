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
        Schema::table('apartments', function (Blueprint $table) {
            $table->enum('ownership_type', ['own', 'lease','lease_back', 'mortgage', 'shared_ownership'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->enum('ownership_type', ['studio', '2bedroom','3bedroom', '4bedroom', 'penthouse', 'duplex']);
        });
    }
};
