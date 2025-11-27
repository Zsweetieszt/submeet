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
        Schema::create('users_logs', function (Blueprint $table) {
            $table->bigIncrements('user_log_id');
            $table->string('ip_address');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('user_log_type', ['Login', 'Logout']);
            $table->json('user_agent');
            $table->timestampTz('created_at', 0)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_logs');
    }
};
