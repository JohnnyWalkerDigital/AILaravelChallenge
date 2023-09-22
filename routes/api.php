<?php

use App\Ticket;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\TicketsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/health', function () {
    return response('OK', 200)
        ->header('Content-Type', 'text/plain');
});

// All /api/tickets routes
Route::prefix('tickets')->controller(TicketsController::class)->group(function () {
    Route::get('/unprocessed','indexUnprocessed');
    Route::get('/processed','indexProcessed');
    Route::get('/user/{email}','getTicketsByUser');
    Route::get('/stats','getStats');
});
