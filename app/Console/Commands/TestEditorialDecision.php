<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Event;
use App\Models\Paper;
use App\Models\Decision;

class TestEditorialDecision extends Command
{
    protected $signature = 'email:test-decision {decision} {event_code?}';
    protected $description = 'Test editorial decision notification email';

    public function handle()
    {
        try {
            $decision = $this->argument('decision');
            $eventCode = $this->argument('event_code');
            
            $emailService = app(EmailService::class);
            
            // Get event
            $event = $eventCode 
                ? Event::where('event_code', $eventCode)->first()
                : Event::first();
            
            if (!$event) {
                $this->error('Event not found');
                return;
            }
            
            // Get author (first user)
            $author = User::first();
            if (!$author) {
                $this->error('No author found');
                return;
            }
            
            // Create dummy objects
            $paper = (object) [
                'title' => 'Test Paper: Advanced Machine Learning Techniques in Academic Conference Management',
                'paper_sub_id' => 'TEST_' . time(),
            ];

            // Create dummy decision object
            $decisionObj = (object) [
                'decision' => $decision,
                'note_for_author' => $this->getDummyFeedback($decision),
            ];

            // Create combined feedback for revisions
            $combinedFeedback = null;
            if (in_array($decision, ['Major Revision', 'Minor Revision'])) {
                $combinedFeedback = $this->getDummyCombinedFeedback($decision);
            }

            $this->info("Testing editorial decision email...");
            $this->info("Event: {$event->event_name}");
            $this->info("Author: {$author->given_name} {$author->family_name} ({$author->email})");
            $this->info("Decision: {$decision}");
            $this->info("Template: " . $emailService->getDecisionTemplateType($decision));
            
            // Send test email
            $emailService->sendAuthorEditorialDecision($author, $paper, $event, $decisionObj, $combinedFeedback);
            
            $this->info('✅ Test editorial decision email sent successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }

    private function getDummyFeedback($decision)
    {
        return match ($decision) {
            'Accept' => 'Congratulations! Your paper demonstrates excellent research quality and makes a significant contribution to the field.',
            'Decline' => 'After careful review, we found several methodological concerns and insufficient evidence to support the main claims. The related work section also needs substantial improvement.',
            'Major Revision' => 'Your paper shows promise but requires substantial revisions. Please address the statistical analysis concerns, expand the literature review, and provide more detailed experimental validation.',
            'Minor Revision' => 'Your paper is well-written and makes a good contribution. Please address minor formatting issues, clarify some technical details, and add a few more recent references.',
            default => 'Test feedback for editorial decision.',
        };
    }

    private function getDummyCombinedFeedback($decision)
    {
        $reviewerFeedback = match ($decision) {
            'Major Revision' => "Reviewer 1: The methodology section needs significant improvement. The experimental setup is not clearly explained and the results lack statistical significance testing.\n\nReviewer 2: While the research question is interesting, the literature review is incomplete and doesn't properly position this work within the existing field.",
            'Minor Revision' => "Reviewer 1: This is a solid paper with good experimental results. Minor improvements needed in the discussion section and some typos should be fixed.\n\nReviewer 2: The paper makes a good contribution. Please clarify the algorithm complexity analysis and add more details about the dataset used.",
            default => "Reviewer 1: General feedback from first reviewer.\n\nReviewer 2: General feedback from second reviewer.",
        };

        $editorNote = $this->getDummyFeedback($decision);
        
        return $reviewerFeedback . "\n\nEditor: " . $editorNote;
    }
}