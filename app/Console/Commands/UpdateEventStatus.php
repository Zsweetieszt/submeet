<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateEventStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-event-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $events = \App\Models\Event::all();

        foreach ($events as $event) {
            $submissionStart = \Carbon\Carbon::parse($event->submission_start);
            $eventEnd = \Carbon\Carbon::parse($event->event_end)->endOfDay();

            if ($now < $submissionStart) {
            $status = 'Upcoming';
            } elseif ($now > $eventEnd) {
            $status = 'Finished';
            } else {
            $status = 'Ongoing';
            }

            $event->event_status = $status;
            $event->save();
        }
    }
}
