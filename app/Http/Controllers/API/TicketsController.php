<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use App\Ticket;
use App\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\ResponseFactory;

class TicketsController extends Controller
{
    public function indexUnprocessed(): LengthAwarePaginator
    {
        return Ticket::unprocessed()->oldest()->paginate(5);
    }

    public function indexProcessed(): LengthAwarePaginator
    {
        return Ticket::processed()->oldest()->paginate(5);
    }

    public function getTicketsByUser(Request $request): LengthAwarePaginator|ResponseFactory
    {
        try {
            $user = User::where('email', $request->email)->firstOrFail();
        } catch (\Exception $e) {
            return response('Resource not found', 404)
                ->header('Content-Type', 'text/plain');
        }

        return Ticket::where('user_id', $user->id)->oldest()->paginate(5);
    }

    /**
     * Get selection of stats.
     *
     * @return array{totalTickets: int, totalUnprocessedTickets: int, userWithMostTickets: User|null, latestProcessedTicketTime: mixed}
     */
    public function getStats(): array
    {
        try {
            $lastProcessedTicketTime = Ticket::processed()->latest()->pluck('updated_at')->firstOrFail();
        } catch (\Exception $e) {
            $lastProcessedTicketTime = null;
        }

        return [
            'totalTickets' => Ticket::count(),
            'totalUnprocessedTickets' => Ticket::unprocessed()->count(),
            'userWithMostTickets' => TicketService::getUserWithMostTickets(),
            'latestProcessedTicketTime' => $lastProcessedTicketTime
        ];
    }
}
