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
        Schema::create('decisions', function (Blueprint $table) {
            $table->bigIncrements('decision_id');
            $table->foreignId('first_paper_sub_id')->nullable()->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('last_paper_sub_id')->nullable()->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('paper_sub_id')->nullable()->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            $table->enum('decision', ['Accept', 'Minor Revision', 'Major Revision', 'Decline', 'Template Revision']);
            $table->foreignId('editor_id')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->string('note_for_author', 3000);
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('set null');
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
