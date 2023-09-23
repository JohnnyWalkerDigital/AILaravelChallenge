<?php

namespace Tests\Feature;

use App\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_console_command_creates_tickets(): void
    {
        \Artisan::call('ticket:create');

        $totalTicketsNum = Ticket::count();

        $this->assertTrue($totalTicketsNum === 1);
    }

    public function test_console_command_processes_tickets(): void
    {
        \Artisan::call('ticket:create 25');
        \Artisan::call('ticket:process');

        $unprocessedTicketsNum = Ticket::unprocessed()->count();

        $this->assertTrue($unprocessedTicketsNum === 20);
    }
}
