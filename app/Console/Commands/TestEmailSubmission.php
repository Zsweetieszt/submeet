<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Event;
use App\Mail\DynamicMail;

class TestEmailSubmission extends Command
{
    protected $signature = 'email:test-submission {event_code?}';
    protected $description = 'Test paper submission email';

    public function handle()
    {
        try {
            $emailService = app(EmailService::class);
            
            // Ambil event (gunakan parameter atau yang pertama)
            $eventCode = $this->argument('event_code');
            $event = $eventCode 
                ? Event::where('event_code', $eventCode)->first()
                : Event::first();
            
            if (!$event) {
                $this->error('Event not found');
                return;
            }
            
            // Ambil user pertama sebagai test author
            $author = User::first();
            
            if (!$author) {
                $this->error('No users found');
                return;
            }
            
            // Create dummy paper object
            $paper = (object) [
                'title' => 'Test Paper: AI Implementation in Conference Management',
                'paper_sub_id' => 'TEST_' . time()
            ];

            $this->info("Sending test email...");
            $this->info("Event: {$event->event_name}");
            $this->info("To: {$author->email}");
            
            $emailService->sendAuthorSubmitPaper($author, $paper, $event);
            
            $this->info('✅ Test email sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}