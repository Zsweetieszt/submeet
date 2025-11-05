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
        Schema::create('topic_papers', function (Blueprint $table) {
            $table->foreignId('topic_id')
                ->constrained('topics', 'topic_id')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreignId('first_paper_sub_id')->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            $table->primary(['topic_id', 'first_paper_sub_id']);
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_papers');
    }
};
