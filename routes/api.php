<?php

use App\Ticket;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/tickets/unprocessed', function () {
    return Ticket::unprocessed()->orderBy('created_at', 'asc')->paginate(5);
});

Route::get('/tickets/processed', function () {
    return Ticket::processed()->orderBy('created_at', 'asc')->paginate(5);
});

Route::get('/tickets/user/{email}', function (Request $request) {
    $user = User::where('email', $request->email)->first();
    return Ticket::where('user_id', $user->id)->orderBy('created_at', 'asc')->paginate(5);
});

Route::get('/tickets/stats', function () {
});
