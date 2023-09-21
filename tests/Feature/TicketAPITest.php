<?php

namespace Tests\Feature;

use App\Ticket;
use App\User;
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

    public function test_api_returns_correct_number_of_unprocessed_tickets()
    {
        Ticket::factory()->generateUser()->generateUser()->count(10)->create();
        Ticket::factory()->generateUser()->generateUser()->processed()->count(25)->create();

        $response = $this->get('/api/tickets/unprocessed');

        $response->assertJson(['total' => 10]);
    }

    public function test_api_returns_correct_number_of_processed_tickets()
    {
        Ticket::factory()->generateUser()->processed()->count(25)->create();
        Ticket::factory()->generateUser()->count(15)->create();

        $response = $this->get('/api/tickets/processed');

        $response->assertJson(['total' => 25]);
    }

    public function test_api_returns_correct_number_of_tickets_for_a_given_email()
    {
        Ticket::factory()->generateUser()->randomStatus()->count(20)->create();
        Ticket::factory()->generateUser()->count(20)->create();
        $user = User::factory()->has(Ticket::factory()->randomStatus()->count(35))->create();

        $response = $this->get('/api/tickets/user/' . $user->email);

        $response->assertJson(['total' => 35]);
    }

    public function test_api_returns_correct_stats()
    {
        $this->markTestIncomplete();
    }
}
