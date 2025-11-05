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
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('event_id');
            $table->string('event_name', 100);
            $table->string('event_shortname', 15)->unique();
            $table->string('event_desc', 255);
            $table->string('event_code', 35)->unique();
            $table->string('event_logo', 255)->nullable();
            $table->text('event_url');
            $table->string('event_organizer', 255);
            $table->foreignId('country_id')->constrained('countries', 'country_id')->onUpdate('cascade')->onDelete('restrict');
            $table->string('manager_name', 255);
            $table->string('manager_contact_ct', 5)->nullable();
            $table->string('manager_contact_email', 255);
            $table->string('manager_contact_number', 15);
            $table->string('support_name', 255)->nullable();
            $table->string('support_contact_ct', 5)->nullable();
            $table->string('support_contact_email', 255)->nullable();
            $table->string('support_contact_number', 15)->nullable();
            $table->string('treasurer_name', 255)->nullable();
            $table->string('treasurer_contact_ct', 5)->nullable();
            $table->string('treasurer_contact_email', 255)->nullable();
            $table->string('treasurer_contact_number', 15)->nullable();
            $table->timestampTz('submission_start', 0);
            $table->timestampTz('submission_end', 0);
            $table->timestampTz('revision_start', 0);
            $table->timestampTz('revision_end', 0);
            $table->timestampTz('join_np_start', 0);
            $table->timestampTz('join_np_end', 0);
            $table->timestampTz('camera_ready_start', 0);
            $table->timestampTz('camera_ready_end', 0);
            $table->timestampTz('payment_start', 0);
            $table->timestampTz('payment_end', 0);
            $table->timestampTz('event_start', 0);
            $table->timestampTz('event_end', 0);
            $table->enum('event_status', ['Upcoming', 'Ongoing', 'Finished', 'Cancelled']);
            $table->foreignId('created_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('updated_by')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('restrict');
            $table->timestampsTz(0);
        });
        DB::statement('ALTER TABLE events ADD COLUMN event_tz INTERVAL NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
