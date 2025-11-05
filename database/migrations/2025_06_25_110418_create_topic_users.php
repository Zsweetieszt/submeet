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
        Schema::create('topic_users', function (Blueprint $table) {
            $table->foreignId('topic_id')
                ->constrained('topics', 'topic_id')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->primary(['topic_id', 'user_id']);
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_users');
    }
};