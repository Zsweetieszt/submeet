<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id(); 
            
        
            $table->unsignedBigInteger('user_id'); 
            
         
            $table->string('plan_code', 50);
           
            $table->string('plan_name', 100);
            $table->decimal('price', 15, 2);
            
            $table->integer('max_events');

            $table->string('desc', 255)->nullable(); 
            
            $table->string('status', 20)->default('pending');
    
            $table->string('payment_method', 50)->default('manual_transfer');
           
            $table->string('payment_proof', 255)->nullable(); 
            
            $table->timestamp('starts_at')->nullable();

            $table->timestamp('ends_at')->nullable();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
};