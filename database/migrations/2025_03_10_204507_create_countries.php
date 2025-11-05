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
        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('country_id');           
            $table->string('country_name', 100);           
            $table->string('iso', 2)->nullable();          
            $table->string('iso3', 3)->nullable();         
            $table->integer('numcode')->nullable();       
            $table->string('phonecode', 10)->nullable();  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
