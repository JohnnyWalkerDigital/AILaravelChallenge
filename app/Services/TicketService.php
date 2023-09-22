<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Return the user with the most tickets (processed and unprocessed).
     *
     * @return User|null
     */
    public static function getUserWithMostTickets(): User|null
    {
        return User::join('tickets', 'users.id', 'tickets.user_id')
            ->select('name', 'email', DB::raw('COUNT(*) AS total'))
            ->groupBy('email')
            ->orderBy('total', 'desc')
            ->first();
    }
}
