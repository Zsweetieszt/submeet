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
        Schema::create('paper_submissions', function (Blueprint $table) {
            $table->bigIncrements('paper_sub_id');
            $table->foreignId('first_paper_sub_id')->nullable()->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('event_id')->constrained('events', 'event_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->tinyInteger('round');
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();
            $table->text('abstract');
            $table->json('authors');
            $table->tinyInteger('corresponding');
            $table->json('keywords');
            $table->string('attach_file', 255);
            $table->string('attach_url', 255)->nullable();
            $table->string('note_for_editor', 3000)->nullable();
            $table->double('similarity')->nullable();
            $table->enum('status', ['Submitted', 'In Review', 'Revision', 'Accepted', 'Declined'])->nullable();
            $table->foreignId('created_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('updated_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->timestampsTz(0);

            // $table->unique(['paper_sub_id', 'first_paper_sub_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paper_submissions');
    }
};
