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
        
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->increments('pay_set_id');
            $table->foreignId('event_id')->nullable()->constrained('events', 'event_id')->onUpdate('cascade')->onDelete('set null');
            $table->string('pay_as_pstr_on_ntl', 75)->nullable();
            $table->string('pay_as_pstr_on_ntl_curr', 10);
            $table->decimal('pay_as_pstr_on_ntl_amount', 15, 2)->nullable();
            $table->string('pay_as_pstr_off_ntl', 75)->nullable();
            $table->string('pay_as_pstr_off_ntl_curr', 10);
            $table->decimal('pay_as_pstr_off_ntl_amount', 15, 2)->nullable();
            $table->string('pay_as_npstr_off_ntl', 75)->nullable();
            $table->string('pay_as_npstr_off_ntl_curr', 10);
            $table->decimal('pay_as_npstr_off_ntl_amount', 15, 2)->nullable();
            $table->string('pay_as_pstr_on_intl', 75)->nullable();
            $table->string('pay_as_pstr_on_intl_curr', 10);
            $table->decimal('pay_as_pstr_on_intl_amount', 15, 2)->nullable();
            $table->string('pay_as_pstr_off_intl', 75)->nullable();
            $table->string('pay_as_pstr_off_intl_curr', 10);
            $table->decimal('pay_as_pstr_off_intl_amount', 15, 2)->nullable();
            $table->string('acc_beneficiary_name', 100);
            $table->string('acc_bank_name', 100);
            $table->string('acc_bank_acc', 50);
            $table->string('acc_swift_code', 25);
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
        Schema::dropIfExists('payment_settings');
    }
};
