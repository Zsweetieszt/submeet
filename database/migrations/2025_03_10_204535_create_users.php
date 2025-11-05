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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('username', 15);
            $table->string('password', 128);
            $table->string('email', 255)->unique();
            $table->string('given_name', 100);
            $table->string('family_name', 100);
            $table->enum('honorif', ['Mr.', 'Mrs.', 'Miss', 'Ms.']);
            $table->string('institution_name', 255)->nullable();
            $table->foreignId('country_id')->constrained('countries', 'country_id')->onUpdate('cascade')->onDelete('restrict');
            $table->string('ct_phone_number_1', 5)->nullable();
            $table->string('phone_number_1', 15);
            $table->string('ct_phone_number_2', 5)->nullable();
            $table->string('phone_number_2', 15)->nullable();
            $table->boolean('root')->default(false);
            $table->boolean('status')->default(false);
            $table->timestampTz('activated_at', 0)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('set null');
            $table->timestampsTz(0);
            $table->timestampTz('first_login_at', 0)->nullable();
            $table->timestampTz('last_login_at', 0)->nullable();
            $table->rememberToken();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestampTz('created_at',0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
