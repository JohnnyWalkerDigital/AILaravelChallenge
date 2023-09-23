<?php

namespace Tests\Feature;

use Event;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_create_is_run_from_scheduler(): void
    {
        Event::fake();

        \Artisan::call('schedule:run');

        Event::assertDispatched(ScheduledTaskFinished::class, function ($event) {
            return str_contains($event->task->command, 'ticket:create');
        });
    }

    public function test_ticket_process_is_run_from_scheduler(): void
    {
        Event::fake();
        $this->travelTo(now()->startOfWeek()->setHour(1)->setMinute(0));

        \Artisan::call('schedule:run');

        Event::assertDispatched(ScheduledTaskFinished::class, function ($event) {
            return str_contains($event->task->command, 'ticket:process');
        });
    }
}
