<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\DynamicMail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send email to author when paper is submitted (round 0)
     */
    public function sendAuthorSubmitPaper($author, $paper, $event)
    {
        try {
            $data = [
                'author_name' => $author->given_name . ' ' . $author->family_name,
                'paper_title' => $paper->title,
                'conference_name' => $event->event_name,
                'paper_id' => $paper->paper_sub_id,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
            ];

            Mail::to($author->email)->queue(new DynamicMail('author_submit_paper', $data));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Send email to editor when new paper is submitted
     */
    public function sendEditorNewPaper($editor, $paper, $event)
    {
        try {
            $data = [
                'editor_name' => $editor->given_name . ' ' . $editor->family_name,
                'paper_title' => $paper->title,
                'author_names' => $this->getAuthorNames($paper),
                'conference_name' => $event->event_name,
                'login_url' => route('login') . '?e=' . $event->event_code,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
            ];

            Mail::to($editor->email)->send(new DynamicMail('editor_new_paper', $data));

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Helper method to get author names from paper
     */
    private function getAuthorNames($paper)
    {
        if (isset($paper->authors)) {
            $authors = json_decode($paper->authors, true);
            if (is_array($authors)) {
                return collect($authors)->pluck('name')->join(', ');
            }
        }
        
        if (method_exists($paper, 'authors') && $paper->authors) {
            return $paper->authors->map(function($author) {
                return $author->given_name . ' ' . $author->family_name;
            })->join(', ');
        }
        
        return 'Unknown Author';
    }

    /**
     * Send email to author when paper is declined during desk evaluation
     */
    public function sendAuthorPaperDeclined($author, $paper, $event, $decision)
    {
        try {
            $data = [
                'author_name' => $author->given_name . ' ' . $author->family_name,
                'paper_title' => $paper->title,
                'conference_name' => $event->event_name,
                'paper_id' => $paper->paper_sub_id,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
                'decline_reasons' => $decision->note_for_author ?? 'No specific feedback provided.',
                'submission_date' => $paper->created_at->format('F d, Y'),
            ];

            Mail::to($author->email)->send(new DynamicMail('author_paper_declined', $data));

        } catch (\Exception $e) {            
            throw $e;
        }
    }

    /**
     * Send email to reviewer when assigned to review a paper
     */
    public function sendReviewerAssignment($reviewer, $paper, $event)
    {
        try {
            $deadlineDate = 'TBD';
            if ($event->revision_end) {
                try {
                    if ($event->revision_end instanceof \Carbon\Carbon) {
                        $deadlineDate = $event->revision_end->format('F d, Y');
                    } else {
                        $deadlineDate = \Carbon\Carbon::parse($event->revision_end)->format('F d, Y');
                    }
                } catch (\Exception $e) {
                    $deadlineDate = 'TBD';
                }
            }
            
            $data = [
                'reviewer_name' => $reviewer->given_name . ' ' . $reviewer->family_name,
                'paper_title' => $paper->title,
                'conference_name' => $event->event_name,
                'paper_id' => $paper->paper_sub_id,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
                'deadline_date' => $deadlineDate,
                'login_url' => route('login') . '?e=' . $event->event_code,
            ];

            Mail::to($reviewer->email)->send(new DynamicMail('reviewer_assignment', $data));

        } catch (\Exception $e) {            
            throw $e;
        }
    }

    /**
     * Send email to editor when reviewer submits a review
     */
    public function sendEditorReviewSubmitted($editor, $reviewer, $paper, $event, $review)
    {
        try {
            $data = [
                'editor_name' => $editor->given_name . ' ' . $editor->family_name,
                'reviewer_name' => $reviewer->given_name . ' ' . $reviewer->family_name,
                'paper_title' => $paper->title,
                'conference_name' => $event->event_name,
                'paper_id' => $paper->paper_sub_id,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
            ];

            Mail::to($editor->email)->send(new DynamicMail('editor_review_submitted', $data));

        } catch (\Exception $e) {            
            throw $e;
        }
    }

    /**
     * Send email to author when editor makes editorial decision
     */
    public function sendAuthorEditorialDecision($author, $paper, $event, $decision, $combinedFeedback = null, $attachmentPath)
    {
        try {
            // Template based on recommendation
            $templateType = $this->getDecisionTemplateType($decision->decision);
            
            $revisionDeadline = null;
            if (in_array($decision->decision, ['Major Revision', 'Minor Revision', 'Template Revision'])) {
                if ($event->revision_end) {
                    try {
                        if ($event->revision_end instanceof \Carbon\Carbon) {
                            $revisionDeadline = $event->revision_end->format('F d, Y');
                        } else {
                            $revisionDeadline = \Carbon\Carbon::parse($event->revision_end)->format('F d, Y');
                        }
                    } catch (\Exception $e) {
                        $revisionDeadline = 'TBD';
                    }
                }
            }
            
            $data = [
                'author_name' => $author,
                'organizer' => $event->event_organizer,
                'paper_title' => $paper->title,
                'conference_name' => $event->event_name,
                'conference_dates' => \Carbon\Carbon::parse($event->event_end)->format('j F Y'),
                'paper_id' => $paper->paper_sub_id,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
                'note_for_author' => $decision->note_for_author,
                'revision_deadline' => $revisionDeadline,
                'similarity' => $paper->similarity ?? 'N/A',
            ];

            switch ($decision->decision) {
                case 'Accept':
                    $data['acceptance_date'] = now()->format('F d, Y');
                    $data['login_url'] = route('login') . '?e=' . $event->event_code;
                    break;
                    
                case 'Decline':
                    $data['decline_reasons'] = $decision->note_for_author;
                    $data['login_url'] = route('login') . '?e=' . $event->event_code;
                    break;
                    
                case 'Major Revision':
                case 'Minor Revision':
                case 'Template Revision':
                    $data['combined_feedback'] = $combinedFeedback ?? $decision->note_for_author;
                    $data['login_url'] = route('login') . '?e=' . $event->event_code;
                    break;
            }

            $mail = new DynamicMail($templateType, $data);
            if ($attachmentPath !== null && file_exists($attachmentPath)) {
                $mail->attach($attachmentPath);
            }

            Mail::to($paper->user->email)->send($mail);

        } catch (\Exception $e) {            
            throw $e;
        }
    }

    /**
     * Get template type based on decision
     */
    public function getDecisionTemplateType($decision)
    {
        $template = match ($decision) {
            'Template Revision' => 'author_decision_template_revision',
            'Decline' => 'author_decision_decline',
            'Major Revision' => 'author_decision_major_revision',
            'Minor Revision' => 'author_decision_minor_revision',
            'Accept' => 'author_decision_accept',
            default => 'author_decision_decline',
        };
        
        return $template;
    }

    /**
     * Get combined feedback from all reviewers and editor
     */
    public function getCombinedFeedback($paper, $editorNote)
    {
        try {
            $feedback = [];
            
            $assignments = \App\Models\Assignment::where('paper_sub_id', $paper->paper_sub_id)->get();
            
            $reviewerCount = 1;
            foreach ($assignments as $assignment) {
                $review = \App\Models\Review::where('assign_id', $assignment->assign_id)->first();
                if ($review && $review->note_for_author) {
                    $feedback[] = "Reviewer {$reviewerCount}: " . $review->note_for_author;
                    $reviewerCount++;
                }
            }
            
            // Tambah note dari editor
            if ($editorNote) {
                $feedback[] = "Editor: " . $editorNote;
            }
            
            return implode("\n\n", $feedback);
            
        } catch (\Exception $e) {            
            return $editorNote ?? 'No feedback available.';
        }
    }

    /**
     * Send email to reviewers when author submits revision
     */
    public function sendReviewerRevisionSubmitted($reviewer, $paper, $event, $revisionRound)
    {
        try {
            $data = [
                'reviewer_name' => $reviewer->given_name . ' ' . $reviewer->family_name,
                'paper_title' => $paper->title,
                'conference_name' => $event->event_name,
                'paper_id' => $paper->paper_sub_id,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
                'revision_round' => $revisionRound,
                'submission_date' => now()->format('F d, Y'),
                'author_names' => $this->getAuthorNames($paper),
                'login_url' => route('login') . '?e=' . $event->event_code,
            ];

            Mail::to($reviewer->email)->send(new DynamicMail('reviewer_revision_submitted', $data));

        } catch (\Exception $e) {            
            throw $e;
        }
    }

    public function sendLoA($paper, $author, $attachmentPath)
    {
        try {
            $data = [
                'author_name' => $author,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
                'paper_title' => $paper->title,
                'conference_name' => $paper->event->event_name,
                'organizer' => $paper->event->event_organizer ?? 'The Organizer',
                'conference_dates' => date('j F Y', strtotime($paper->event->event_start)) . ' - ' . date('j F Y', strtotime($paper->event->event_end)),
            ];
    
            $mail = new DynamicMail('author_send_loa', $data);
            if ($attachmentPath && file_exists($attachmentPath)) {
                $mail->attach($attachmentPath);
            }
    
            Mail::to($paper->user->email)->send($mail);
    
        } catch (\Exception $e) {
            \Log::error('Failed to send LoA email', [
                'error' => str_replace(["\r", "\n"], ' ', $e->getMessage()),
                'trace' => $e->getTraceAsString(),
                'paper_id' => $paper->paper_sub_id,
                'email' => $paper->user->email,
                'attachmentPath' => $attachmentPath,
            ]);
            throw $e;
        }
    }

    public function sendLoI($paper, $author, $attachmentPath)
    {
        try {
            $data = [
                'author_name' => $author,
                'first_paper_sub_id' => $paper->first_paper_sub_id ?? $paper->paper_sub_id,
                'paper_title' => $paper->title,
                'conference_name' => $paper->event->event_name,
                'conference_date' => $paper->event->event_start === $paper->event->event_end
                    ? date('j F Y', strtotime($paper->event->event_start))
                    : date('j F Y', strtotime($paper->event->event_start)) . ' - ' . date('j F Y', strtotime($paper->event->event_end)),
            ];
    
            $mail = new DynamicMail('author_send_loi', $data);
            if ($attachmentPath && file_exists($attachmentPath)) {
                $mail->attach($attachmentPath);
            }
    
            Mail::to($paper->user->email)->send($mail);
    
        } catch (\Exception $e) {
            \Log::error('Failed to send LoI email', [
                'error' => str_replace(["\r", "\n"], ' ', $e->getMessage()),
                'trace' => $e->getTraceAsString(),
                'paper_id' => $paper->paper_sub_id,
                'email' => $paper->user->email,
                'attachmentPath' => $attachmentPath,
            ]);
            throw $e;
        }
    }

    public function sendLoINP($participant, $attachmentPath, $event)
    {
        try {
            $data = [
                'author_name' => $participant->given_name . ' ' . $participant->family_name,
                'conference_name' => $event->event_name,
                'conference_date' => $event->event_start === $event->event_end
                    ? date('j F Y', strtotime($event->event_start))
                    : date('j F Y', strtotime($event->event_start)) . ' - ' . date('j F Y', strtotime($event->event_end)),
                'first_paper_sub_id' => ''
            ];
    
            $mail = new DynamicMail('author_send_loi_np', $data);
            if ($attachmentPath && file_exists($attachmentPath)) {
                $mail->attach($attachmentPath);
            }
    
            Mail::to($participant->email)->send($mail);
    
        } catch (\Exception $e) {
            \Log::error('Failed to send LoI email', [
                'error' => str_replace(["\r", "\n"], ' ', $e->getMessage()),
                'trace' => $e->getTraceAsString(),
                'email' => $participant->email,
                'attachmentPath' => $attachmentPath,
            ]);
            throw $e;
        }
    }

    public function sendInternationalPaymentEmail($user, $event, $paymentDetails)
    {
        try {
            $data = [
                'list_author' => is_object($user) && isset($user->given_name) ? "{$user->given_name} {$user->family_name}" : (is_string($user) ? explode(' - ', $user)[0] : $user),
                'role' => $paymentDetails->role ?? 'Participant',
                'conference_name' => $event->event_name,
                'payment_currency' => $paymentDetails->currency ?? 'USD',
                'payment_amount' => $paymentDetails->amount ?? '0.00',
                'payment_end_date' => date('j F Y', strtotime($event->payment_end)),
                'conference_short_name' => $event->event_shortname ?? $event->event_name,
                'conference_year' => date('Y', strtotime($event->event_start)),
                'attendance_mode' => $paymentDetails->is_offline ? 'Offline' : 'Online',
                'bank_name' => $paymentDetails->bank_name ?? 'Unknown Bank',
                'account_name' => $paymentDetails->account_name ?? 'Unknown Account',
                'account_number' => $paymentDetails->account_number ?? 'Unknown Number',
                'swift_code' => $paymentDetails->swift_code ?? 'Unknown SWIFT',
                'first_paper_sub_id' => $paymentDetails->first_paper_sub_id ?? '',
            ];

            $mail = new DynamicMail('international_payment', $data);
            Mail::to(auth()->user()->email)->send($mail);

        } catch (\Exception $e) {
            \Log::error('Failed to send international payment email', [
                'error' => $e->getMessage(),
                'email' => auth()->user()->email,
            ]);
            throw $e;
        }
    }

    public function sendNationalPaymentEmail($user, $event, $paymentDetails)
    {
        try {
            $data = [
                'list_author' => is_object($user) && isset($user->given_name) ? "{$user->given_name} {$user->family_name}" : (is_string($user) ? explode(' - ', $user)[0] : $user),
                'role' => $paymentDetails->role ?? 'Participant',
                'conference_name' => $event->event_name,
                'payment_currency' => $paymentDetails->currency ?? 'IDR',
                'payment_amount' => $paymentDetails->amount ?? '0.00',
                'payment_end_date' => date('j F Y', strtotime($event->payment_end)),
                'conference_short_name' => $event->event_shortname ?? $event->event_name,
                'conference_year' => date('Y', strtotime($event->event_start)),
                'attendance_mode' => $paymentDetails->is_offline ? 'Offline' : 'Online',
                'briva_number' => $paymentDetails->payment_history->brivano ?? 'Unknown BRIVA',
                'first_paper_sub_id' => $paymentDetails->first_paper_sub_id ?? '',
            ];

            $mail = new DynamicMail('national_payment', $data);
            Mail::to(auth()->user()->email)->send($mail);

        } catch (\Exception $e) {
            \Log::error('Failed to send national payment email', [
                'error' => $e->getMessage(),
                'email' => auth()->user()->email,
            ]);
            throw $e;
        }
    }
}