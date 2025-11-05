<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\User;
use App\Services\EmailService;

class sendLoI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loi:send 
                           {event_id : Event ID}
                           {participant_id : Participant User ID} 
                           {loi_filename : LoI filename (with extension)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Letter of Invitation using existing file';

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->argument('event_id');
        $participantId = $this->argument('participant_id');
        $loiFilename = $this->argument('loi_filename');

        try {
            // Get event
            $event = Event::find($eventId);
            if (!$event) {
                $this->error("Event with ID '{$eventId}' not found.");
                return 1;
            }

            // Get participant
            $participant = User::find($participantId);
            if (!$participant) {
                $this->error("Participant with ID '{$participantId}' not found.");
                return 1;
            }

            // Check if LoI file exists
            $loiFilePath = storage_path("app/public/loi/{$loiFilename}");
            if (!file_exists($loiFilePath)) {
                $this->error("LoI file not found at: {$loiFilePath}");
                return 1;
            }

            $this->info("Sending LoI to: {$participant->given_name} {$participant->family_name} ({$participant->email})");
            $this->info("Event: {$event->event_name}");
            $this->info("Using file: {$loiFilename}");

            // Send email with existing LoI file
            $this->emailService->sendLoINP($participant, $loiFilePath, $event);

            $this->info("✓ LoI email sent successfully!");
            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to send LoI: {$e->getMessage()}");
            return 1;
        }
    }
}