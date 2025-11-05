<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Event;

class TestEmailReviewerAssignment extends Command
{
    protected $signature = 'email:test-reviewer {event_code?}';
    protected $description = 'Test reviewer assignment email';

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
                'title' => 'Test Paper: Machine Learning Applications in Conference Management',
                'paper_sub_id' => 'TEST_' . time(),
            ];

            $this->info("Sending test reviewer assignment email...");
            $this->info("Event: {$event->event_name}");
            $this->info("To: {$reviewer->email}");
            $this->info("Reviewer: {$reviewer->given_name} {$reviewer->family_name}");
            
            $emailService->sendReviewerAssignment($reviewer, $paper, $event);
            
            $this->info('✅ Test reviewer assignment email sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}