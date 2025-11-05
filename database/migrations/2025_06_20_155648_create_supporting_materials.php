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
        Schema::create('supporting_materials', function (Blueprint $table) {
            $table->bigIncrements('supp_material_id');
            $table->string('slide_file', 255);
            $table->string('poster_file', 255);
            $table->string('video_url', 255);
            $table->foreignId('first_paper_sub_id')->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('event_id')->constrained('events', 'event_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('updated_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_materials');
    }
};
