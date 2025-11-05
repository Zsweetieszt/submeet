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
        Schema::create('review_items', function (Blueprint $table) {
            $table->bigIncrements('review_item_id');
            $table->string('name', 255);
            $table->text('desc');
            $table->tinyInteger('weight');
            $table->tinyInteger('seq')->nullable();
            $table->foreignId('event_id')->nullable()->constrained('events', 'event_id')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('updated_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->timestampsTz(0);
        });

        Schema::create('review_options', function (Blueprint $table) {
            $table->bigIncrements('review_option_id');
            $table->foreignId('review_item_id')->nullable()->constrained('review_items', 'review_item_id')->onUpdate('cascade')->onDelete('set null');
            $table->tinyInteger('scale');
            $table->string('desc', 255);
            $table->foreignId('created_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('updated_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->timestampsTz(0);
        });

        Schema::create('review_contents', function (Blueprint $table){
            $table->bigIncrements('review_content_id');
            $table->foreignId('review_item_id')->nullable()->constrained('review_items', 'review_item_id')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('review_id')->constrained('reviews', 'review_id')->onUpdate('cascade')->onDelete('restrict');
            $table->tinyInteger('value');
            $table->foreignId('created_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('updated_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->timestampsTz(0);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
            // $table->string('external_id', 125)->nullable();
            // $table->string('user_id', 125)->nullable();
            // $table->boolean('is_high')->nullable();
            // $table->string('payment_method', 125)->nullable();
            $table->string('presenter', 100)->nullable();
            $table->foreignId('nationality_country_id')->nullable()->constrained('countries', 'country_id')->onUpdate('cascade')->onDelete('restrict');
            $table->boolean('is_offline')->nullable();
            $table->enum('status', ['Paid', 'Unpaid', 'Pending'])->nullable();
            // $table->string('merchant_name', 125)->nullable();
            // $table->decimal('amount', 15, 2)->nullable();
            // $table->decimal('paid_amount', 15, 2)->nullable();
            // $table->string('bank_code', 125)->nullable();
            // $table->string('payer_email', 255)->nullable();
            // $table->string('description', 255)->nullable();
            // $table->decimal('adjusted_received_amount', 15, 2)->nullable();
            // $table->decimal('fees_paid_amount', 15, 2)->nullable();
            // $table->timestampTz('resp_created_at', 0);
            // $table->timestampTz('resp_updated_at', 0);
            // $table->string('currency', 125)->nullable();
            // $table->string('payment_channel', 125)->nullable();
            // $table->string('payment_destination', 75)->nullable();
            $table->foreignId('paid_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('event_id')->constrained('events', 'event_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('first_paper_sub_id')->nullable()->constrained('paper_submissions', 'paper_sub_id')->onUpdate('cascade')->onDelete('restrict');
            // $table->foreignId('created_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            // $table->foreignId('updated_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');

            $table->timestampsTz(0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('review_contents');
        Schema::dropIfExists('review_options');
        Schema::dropIfExists('review_items');
    }
};
