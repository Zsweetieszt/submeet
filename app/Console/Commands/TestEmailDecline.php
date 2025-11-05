<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Event;
use App\Models\Decision;

class TestEmailDecline extends Command
{
    protected $signature = 'email:test-decline {event_code?}';
    protected $description = 'Test author paper declined email';

    public function handle()
    {
        try {
            $emailService = app(EmailService::class);
            
            $eventCode = $this->argument('event_code');
            $event = $eventCode 
                ? Event::where('event_code', $eventCode)->first()
                : Event::first();
            
            if (!$event) {
                $this->error('Event not found');
                return;
            }
            
            $author = User::first();
            
            if (!$author) {
                $this->error('No author found');
                return;
            }
            
            // Create dummy paper and decision objects
            $paper = (object) [
                'title' => 'Test Paper: AI Implementation in Conference Management',
                'paper_sub_id' => 'TEST_' . time(),
                'created_at' => now()
            ];

            $decision = (object) [
                'note_for_author' => "Dear Author,\n\nAfter careful review, we found the following issues:\n\n1. The methodology section lacks sufficient detail\n2. The literature review does not adequately address recent developments\n3. The experimental results need more statistical analysis\n\nWe encourage you to address these concerns and consider resubmission to future conferences."
            ];

            $this->info("Sending test decline email...");
            $this->info("Event: {$event->event_name}");
            $this->info("To: {$author->email}");
            
            $emailService->sendAuthorPaperDeclined($author, $paper, $event, $decision);
            
            $this->info('✅ Test decline email sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}