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
        Schema::create('topics', function (Blueprint $table) {
            $table->bigIncrements('topic_id');
            $table->string('topic_name', 100);
            $table->foreignId('event_id')->constrained('events', 'event_id')->onUpdate('cascade')->onDelete('restrict');
            $table->unique(['topic_name', 'event_id']);
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
