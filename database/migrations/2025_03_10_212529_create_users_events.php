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
        Schema::create('users_events', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('event_id')->constrained('events', 'event_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('role_id')->constrained('roles', 'role_id')->onUpdate('cascade')->onDelete('restrict');
            $table->boolean('is_offline')->default(true);
            $table->primary(['user_id', 'event_id', 'role_id']);
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_events');
    }
};
