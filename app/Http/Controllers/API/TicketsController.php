<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use App\Ticket;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function getTicketsByUser(Request $request): LengthAwarePaginator
    {
        $user = User::where('email', $request->email)->first();
        return Ticket::where('user_id', $user->id)->oldest()->paginate(5);
    }

    public function getStats(): array
    {
        return [
            'totalTickets' => Ticket::count(),
            'totalUnprocessedTickets' => Ticket::unprocessed()->count(),
            'userWithMostTickets' => TicketService::getUserWithMostTickets(),
            'latestProcessedTicketTime' => Ticket::processed()->latest()->pluck('updated_at')->first()
        ];
    }
}
