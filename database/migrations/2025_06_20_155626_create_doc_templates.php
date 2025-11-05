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
        Schema::create('doc_templates', function (Blueprint $table) {
            $table->bigIncrements('doc_tpl_id');
            $table->string('tpl_paper_doc_file', 255);
            $table->string('tpl_paper_latex_file', 255);
            $table->string('tpl_copyright_trf_file', 255);
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
        Schema::dropIfExists('doc_templates');
    }
};
