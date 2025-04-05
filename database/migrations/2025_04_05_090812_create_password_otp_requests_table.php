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
        Schema::create('password_otp_requests', function (Blueprint $table) {
            $table->increments('password_otp_request_id');
            $table->unsignedBigInteger('user_id');
            $table->string('otp_code');
            $table->string('new_password'); // bcrypt
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_otp_requests');
    }
};
