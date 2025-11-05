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
        Schema::create('payment_history', function (Blueprint $table) {
            $table->bigIncrements('payment_history_id');
            $table->foreignId('payment_id')->nullable()->constrained('payments', 'payment_id')->onUpdate('cascade')->onDelete('restrict');
            $table->string('brivano', 50)->nullable();
            $table->timestampTz('expired_date', 0)->nullable();
            $table->string('receipt', length: 255)->nullable();
            $table->string('desc', length: 255)->nullable();
            $table->timestampTz('upload_receipt_at', 0)->nullable();
            $table->timestampsTz(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_history');
    }
};
