<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Event;

class TestEmailRevisionSubmitted extends Command
{
    protected $signature = 'email:test-revision-submitted {event_code?}';
    protected $description = 'Test reviewer revision submitted notification email';

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
            
            $reviewer = User::first();
            
            if (!$reviewer) {
                $this->error('No reviewer found');
                return;
            }
            
            // Create dummy paper object
            $paper = (object) [
                'title' => 'Test Paper: Advanced Machine Learning Techniques in Academic Conference Management (Revised)',
                'paper_sub_id' => 'TEST_REV_' . time(),
                'authors' => json_encode([
                    ['name' => 'John Doe', 'email' => 'john@example.com'],
                    ['name' => 'Jane Smith', 'email' => 'jane@example.com']
                ])
            ];

            $revisionRound = 2;

            $this->info("Sending test revision submission notification email...");
            $this->info("Event: {$event->event_name}");
            $this->info("Reviewer: {$reviewer->given_name} {$reviewer->family_name} ({$reviewer->email})");
            $this->info("Paper: {$paper->title}");
            $this->info("Revision Round: {$revisionRound}");
            
            $emailService->sendReviewerRevisionSubmitted($reviewer, $paper, $event, $revisionRound);
            
            $this->info('✅ Test revision submission notification email sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}