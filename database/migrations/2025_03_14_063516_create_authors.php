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
        Schema::create('authors', function (Blueprint $table) {
            $table->bigIncrements('paper_author_id');
            $table->foreignId('paper_sub_id')->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            $table->string('email', 255);
            $table->string('given_name', 100);
            $table->string('family_name', 100);
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->tinyInteger('order');
            $table->boolean('is_corresponding');
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
        Schema::dropIfExists('authors');
    }
};
