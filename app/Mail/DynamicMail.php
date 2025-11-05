<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicMail extends Mailable
{
    use Queueable, SerializesModels;

    public $templateType;
    public $data;

    public function __construct($templateType, $data)
    {
        $this->templateType = $templateType;
        $this->data = $data;
    }

    public function build()
    {
        $config = $this->getTemplateConfig();
        
        return $this->subject($config['subject'])
                    ->view($config['view'])
                    ->with($this->data);
    }

    private function getTemplateConfig()
    {
        $templates = [
            'author_submit_paper' => [
                'subject' => 'Paper Submission Confirmation – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.submit_paper'
            ],
            'editor_new_paper' => [
                'subject' => 'New Paper Submitted – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.editor.new_paper'
            ],
            'author_paper_declined' => [
                'subject' => 'Paper Submission Outcome – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.paper_declined'
            ],
            'reviewer_assignment' => [
                'subject' => 'Review Invitation – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.reviewer.assignment'
            ],
            'editor_review_submitted' => [
                'subject' => 'Review Submitted – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.editor.review_submitted'
            ],
            'author_decision_decline' => [
                'subject' => 'Editorial Decision – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.decision_decline'
            ],
            'author_decision_major_revision' => [
                'subject' => 'Editorial Decision: Major Revisions Required – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.decision_major_revision'
            ],
            'author_decision_minor_revision' => [
                'subject' => 'Editorial Decision: Minor Revisions Required – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.decision_minor_revision'
            ],
            'author_decision_template_revision' => [
                'subject' => 'Editorial Decision: Template Revision Required – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.decision_template_revision'
            ],
            'author_decision_accept' => [
                'subject' => 'Paper Accepted – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.decision_accept'
            ],
            'reviewer_revision_submitted' => [
                'subject' => 'Revised Paper Submitted – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.reviewer.revision_submitted'
            ],
            'author_send_loa' => [
                'subject' => 'Paper Accepted – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.send_loa'
            ],
            'author_send_loi' => [
                'subject' => 'Letter of Invitation – Paper ID: ' . $this->data['first_paper_sub_id'] . ' - ' . $this->data['conference_name'],
                'view' => 'emails.author.send_loi'
            ],
            'author_send_loi_np' => [
                'subject' => 'Letter of Invitation - Non Presenter Participant' . $this->data['conference_name'],
                'view' => 'emails.author.send_loi_np'
            ],
            'international_payment' => [
                'subject' => 'ISSAT 2025 Conference Payment Information',
                'view' => 'emails.author.international_payment'
            ],
            'national_payment' => [
                'subject' => 'ISSAT 2025 Conference Payment Information',
                'view' => 'emails.author.national_payment'
            ],
        ];

        return $templates[$this->templateType] ?? [
            'subject' => 'SubMeet Notification',
            'view' => 'emails.default'
        ];
    }
}