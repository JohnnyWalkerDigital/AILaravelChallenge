## AddIntel Laravel Challenge

Johnny Walker ([johnny@johnnywalkerdigital.com](mailto:johnny@johnnywalkerdigital.com))

### Instructions for running

Ensure all required packages have been installed:
 - `composer install`
 - `npm install`

Tests can be run and code assessed with PHP Sniffer and Larastan by running:
 - `composer check`

(PHPStan is set to Level 9.)

### Custom console commands

Create ticket every every minute:

`php artisan ticket:create --repeat`

Process 5 tickets every five minutes:

`php artisan ticket:process --repeat`

### API Endpoints

Return paginated list of all unprocessed tickets:

`/api/tickets/unprocessed`

Return paginated list of processed tickets:

`/api/tickets/process`

Return paginated list of all tickets belonging to an email address:

`/api/tickets/user/{email}`

Return overall ticket stats:

`/api/tickets/stats`

---

### Original task list

- Create a console command that generates a ticket with dummy data every minute. A ticket should have the following fields:
    - Ticket Subject
    - Ticket Content
    - Name of the user who submitted the ticket
    - Email of the user who submitted the ticket
    - Time when the ticket was added
    - Status of the ticket (`boolean`) - Set to `false` by default to indicate that the ticket is not processed.
- Create another console command that processes five tickets every five minutes. Tickets should be processed in chronological order. Changing the status value to true would be considered as processing of the ticket.
- Create API endpoints that can provide the following functionality:
    - Return a paginated list of all unprocessed tickets (i.e. all tickets with status set to `false`).
    - Return a paginated list of all processed tickets (i.e. all tickets with status set to `true`).
    - Return a paginated list of all tickets that belong to the user with the corresponding email address.
    - Return the following stats:
        - total number of tickets in the database
        - total number of unprocessed tickets in the database
        - name of the user who submitted the highest number of tickets (count by email)
        - time when the last processing of a ticket was done.
