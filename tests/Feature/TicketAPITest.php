<?php

namespace Tests\Feature;

use App\Ticket;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TicketAPITest extends TestCase
{
    use RefreshDatabase;

    public function test_api_route_healthy(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200);
    }

    public function test_api_returns_correct_number_of_unprocessed_tickets(): void
    {
        // Create unprocessed tickets
        Ticket::factory()->generateUser()->generateUser()->count(10)->create();
        // Create processed tickets
        Ticket::factory()->generateUser()->generateUser()->processed()->count(5)->create();

        $response = $this->get('/api/tickets/unprocessed');

        // Check number of records matches unprocessed
        $response->assertJson(['total' => 10]);
    }

    public function test_api_returns_correct_number_of_processed_tickets(): void
    {
        // Create processed tickets
        Ticket::factory()->generateUser()->processed()->count(10)->create();
        // Create unprocessed tickets
        Ticket::factory()->generateUser()->count(5)->create();

        $response = $this->get('/api/tickets/processed');

        // Check number of records matches processed
        $response->assertJson(['total' => 10]);
    }

    public function test_api_returns_correct_number_of_tickets_for_user(): void
    {
        // Create both processed and unprocessed tickets with various users
        Ticket::factory()->generateUser()->randomStatus()->count(50)->create();
        // Create tickets with a new user
        $user = User::factory()->has(Ticket::factory()->randomStatus()->count(35))->create();

        $response = $this->get('/api/tickets/user/' . $user->email);

        // Check number of records returned matches created by user
        $response->assertJson(['total' => 35]);
    }

    public function test_api_returns_correct_response_for_non_user(): void
    {
        $response = $this->get('/api/tickets/user/' . 'fake@email.com');

        $response->assertStatus(404);
    }

    public function test_api_returns_correct_total_tickets_stat(): void
    {
        // Create unprocessed tickets
        Ticket::factory()->generateUser()->generateUser()->count(15)->create();
        // Create processed tickets
        Ticket::factory()->generateUser()->generateUser()->processed()->count(15)->create();

        $response = $this->get('/api/tickets/stats/');

        $response->assertJson(['totalTickets' => 30]);
    }

    public function test_api_returns_correct_user_with_most_tickets_stat(): void
    {
        // Create unprocessed tickets
        Ticket::factory()->generateUser()->generateUser()->count(15)->create();
        // Create processed tickets
        Ticket::factory()->generateUser()->generateUser()->processed()->count(15)->create();
        // Create tickets with a new user
        $user = User::factory()->has(Ticket::factory()->randomStatus()->count(50))->create();

        $response = $this->get('/api/tickets/stats/');

        $response->assertJson(['userWithMostTickets'
            => [
                'name' => $user->name,
                'email' => $user->email,
                'total' => 50
            ]
        ]);
    }

    public function test_api_returns_correct_number_of_unprocessed_tickets_stat(): void
    {
        // Create unprocessed tickets
        Ticket::factory()->generateUser()->generateUser()->count(25)->create();
        // Create processed tickets
        Ticket::factory()->generateUser()->generateUser()->processed()->count(15)->create();

        $response = $this->get('/api/tickets/stats/');

        $response->assertJson(['totalUnprocessedTickets' => 25]);
    }

    public function test_api_returns_correct_last_processed_ticket_time_stat(): void
    {
        // Create unprocessed tickets
        Ticket::factory()->generateUser()->generateUser()->count(25)->create();
        // Create processed tickets
        Ticket::factory()->generateUser()->generateUser()->processed()->count(15)->create();
        // Jump to future date
        $this->travel(1)->year();
        // Freeze time for test and truncate milliseconds (which aren't stored)
        Carbon::setTestNow(now()->format('Y-m-d H:i:s'));
        // Process ticket
        \Artisan::call('ticket:process 1');

        $response = $this->get('/api/tickets/stats/');

        $response->assertJson(['latestProcessedTicketTime' => now()->toISOString()]);
    }

    public function test_api_returns_correct_response_for_missing_last_processed_stat(): void
    {
        $response = $this->get('/api/tickets/stats/');

        $response->assertJson(['latestProcessedTicketTime' => null]);
    }
}
