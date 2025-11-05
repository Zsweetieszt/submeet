<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;
use App\Models\Event;

class TestEmailEditor extends Command
{
    protected $signature = 'email:test-editor {event_code?}';
    protected $description = 'Test editor new paper email';

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
            
            // PERBAIKAN: Cari editor berdasarkan relasi user_events
            $editor = User::whereHas('user_events', function($query) use ($event) {
                $query->where('event_id', $event->event_id)
                      ->whereHas('role', function($roleQuery) {
                          $roleQuery->where('role_name', 'Editor');
                      });
            })->first();
            
            // Jika tidak ada editor di event ini, ambil user dengan role editor dari event manapun
            if (!$editor) {
                $editor = User::whereHas('user_events', function($query) {
                    $query->whereHas('role', function($roleQuery) {
                        $roleQuery->where('role_name', 'Editor');
                    });
                })->first();
            }
            
            // Fallback: ambil user pertama jika tidak ada editor
            if (!$editor) {
                $editor = User::first();
                $this->warn('No editor found, using first user as fallback');
            }
            
            if (!$editor) {
                $this->error('No users found');
                return;
            }
            
            // Create dummy paper object
            $paper = (object) [
                'title' => 'Test Paper: AI Implementation in Conference Management',
                'paper_sub_id' => 'TEST_' . time(),
                'authors' => json_encode([
                    ['name' => 'John Doe', 'email' => 'john@example.com'],
                    ['name' => 'Jane Smith', 'email' => 'jane@example.com']
                ])
            ];

            $this->info("Sending test email to editor...");
            $this->info("Event: {$event->event_name}");
            $this->info("To: {$editor->email}");
            $this->info("Editor: {$editor->given_name} {$editor->family_name}");
            
            $emailService->sendEditorNewPaper($editor, $paper, $event);
            
            $this->info('✅ Test email sent successfully to editor!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}