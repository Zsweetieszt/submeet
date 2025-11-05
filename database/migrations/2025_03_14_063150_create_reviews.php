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
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('review_id');
            $table->foreignId('assign_id')->constrained('assignments', 'assign_id')->onUpdate('cascade')->onDelete('restrict');
            $table->text('note_for_author');
            $table->string('note_for_editor', 3000)->nullable();
            $table->string('attach_file', 255)->nullable();
            $table->string('attach_url', 255)->nullable();
            $table->enum('recommendation', ['Decline', 'Accept', 'Minor Revisions', 'Major Revisions']);
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
