<?php

namespace App\Console\Commands;

use App\Ticket;
use Illuminate\Console\Command;

class TicketProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:process
                            {amount=5 : Number of tickets to process}
                            {--R|repeat : Repeat every 5 minutes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the oldest unprocessed tickets';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sleepSeconds = 60 * 5;
        $amount = intval($this->argument('amount'));
        $repeat = $this->option('repeat');

        // This is a very odd requirement and I wouldn't normally do this
        // Ideally this command would be repeated through the scheduler
        // (This has been set up in Kernal.php)
        if ($repeat) {
            $this->comment('Repeating every ' . $sleepSeconds . ' seconds...');

            while (true) {
                $result = self::processTickets($amount);
                $this->info($result);

                sleep($sleepSeconds);
            }
        }

        // If not repeating forever, run once
        $result = self::processTickets($amount);
        $this->info($result);

        return 0;
    }

    private static function processTickets(int $amount): string
    {
        $numProcessed = Ticket::unprocessed()->orderBy('created_at', 'asc')->limit($amount)
            ->update(['status' => true]);
        return 'Processed ' . $numProcessed . ' ticket' . ($numProcessed != 1 ? 's' : '');
    }
}
