<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Paper;
use Illuminate\Console\Command;
use App\Services\EmailService;
use NcJoes\OfficeConverter\OfficeConverter;
use PhpOffice\PhpWord\TemplateProcessor;

class TestEmailLoI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email-lo-i {event_code} {paper_sub_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Letter of Invitation (LoI) via email';

    /**
     * EmailService instance.
     *
     * @var EmailService
     */
    protected $emailService;

    /**
     * Create a new command instance.
     *
     * @param EmailService $emailService
     */
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
        $event_code = $this->argument('event_code');
        $paper_sub_id = $this->argument('paper_sub_id');

        $this->info('Starting to send LoI email...');
        $this->sendEmailWithAttachment($event_code, $paper_sub_id);
    }

    /**
     * Send email with LoI attachment.
     *
     * @param string $event_code
     * @param int $paper_sub_id
     */
    public function sendEmailWithAttachment($event_code, $paper_sub_id)
    {
        try {
            // Retrieve event and paper based on event code and paper ID
            $event = Event::where('event_code', $event_code)->firstOrFail();
            $paper = Paper::with(['event', 'user'])->where('paper_sub_id', $paper_sub_id)->firstOrFail();

            // Path to the template file
            $templatePath = public_path('assets/template/LoI_ISSAT_2025.docx');
            if (!file_exists($templatePath)) {
                throw new \Exception('Template file not found: ' . $templatePath);
            }

            // Generate file using TemplateProcessor
            $phpWord = new TemplateProcessor($templatePath);
            $phpWord->setValue('Conference Name', $event->event_name);
            $phpWord->setValue('Conference Dates', date('d F Y', strtotime($event->event_start)) . ' - ' . date('d F Y', strtotime($event->event_end)));
            $phpWord->setValue('Date Sent', date('d F Y'));
            $phpWord->setValue('Author', $paper->user->given_name . ' ' . $paper->user->family_name);
            $phpWord->setValue('Paper Title', $paper->title);

            // Define output directory and file name
            $outputDir = storage_path('app/public/loi');
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            $fileName = 'LoI_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $paper->title) . '_' . $paper_sub_id . '_' . date('Y-m-d') . '.docx';
            $filePath = $outputDir . '/' . $fileName;

            // Save Word file
            $phpWord->saveAs($filePath);

            // Convert to PDF using OfficeConverter
            if (!isset($_SERVER['HOME'])) {
                $_SERVER['HOME'] = getenv('HOME') ?: (getenv('HOMEDRIVE') . getenv('HOMEPATH'));
            }
            $converter = new OfficeConverter($filePath);
            $converter->convertTo(basename($fileName, '.docx') . '.pdf'); // Output to the same directory

            $pdfFilePath = $outputDir . '/' . basename($fileName, '.docx') . '.pdf';
            if (file_exists($filePath)) {
                unlink($filePath); // Delete Word file after converting to PDF
            }

            // Send email using EmailService
            $this->emailService->sendLoI($paper, $paper->user, $pdfFilePath);

            $this->info('Email with attachment sent successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to send email with attachment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Failed to send email: ' . $e->getMessage());
        }
    }
}