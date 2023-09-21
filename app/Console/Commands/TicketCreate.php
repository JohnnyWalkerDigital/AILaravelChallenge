<?php

namespace App\Console\Commands;

use App\Ticket;
use App\User;
use Illuminate\Console\Command;

class TicketCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:create
                            {amount=1 : Number of tickets to create (default 1)}
                            {--R|repeat : Repeat every minute}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tickets using dummy data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sleepSeconds = 60;
        $amount = intval($this->argument('amount'));
        $repeat = $this->option('repeat');

        // This is a very odd requirement and I wouldn't normally do this
        // Ideally this command would be repeated through the scheduler
        // (This has been set up in Kernel.php)
        if ($repeat) {
            $this->comment('Repeating every ' . $sleepSeconds . ' seconds...');

            while (true) {
                $result = self::createTicket($amount);
                $this->info($result);

                sleep($sleepSeconds);
            }
        }

        // If not repeating forever, run once
        $result = self::createTicket($amount);
        $this->info($result);

        return 0;
    }

    private static function createTicket(int $amount): string
    {
        $amount = count(Ticket::Factory()->generateUser()->count($amount)->create());
        return 'Created ' . $amount . ' ticket' . ($amount != 1 ? 's' : '') . ' using dummy data';
    }
}
