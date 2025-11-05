<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Event;

class TestEmailReviewSubmitted extends Command
{
    protected $signature = 'email:test-review-submitted {event_code?}';
    protected $description = 'Test editor review submitted notification email';

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
            
            $editor = User::first();
            $reviewer = User::skip(1)->first();
            
            if (!$editor || !$reviewer) {
                $this->error('No editor or reviewer found');
                return;
            }
            
            // Create dummy objects
            $paper = (object) [
                'title' => 'Test Paper: Advanced Machine Learning Techniques in Academic Conference Management',
                'paper_sub_id' => 'TEST_' . time(),
            ];

            $review = (object) [
                'recommendation' => 'Minor Revisions',
            ];

            $this->info("Sending test review submission notification email...");
            $this->info("Event: {$event->event_name}");
            $this->info("Editor: {$editor->given_name} {$editor->family_name} ({$editor->email})");
            $this->info("Reviewer: {$reviewer->given_name} {$reviewer->family_name}");
            
            $emailService->sendEditorReviewSubmitted($editor, $reviewer, $paper, $event, $review);
            
            $this->info('✅ Test review submission notification email sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}