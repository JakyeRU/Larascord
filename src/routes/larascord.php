<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use Jakyeru\Larascord\Http\Controllers\DiscordController;

Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . config('larascord.client_id')
    . '&redirect_uri=' . config('larascord.redirect_uri')
    . '&response_type=code&scope=' . implode('%20', explode('&', config('larascord.scopes')))
    . '&prompt=' . config('larascord.prompt', 'none'))
    ->name('login');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware(['web', 'auth'])
    ->name('password.confirm');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['web', 'auth'])
    ->name('logout');

Route::group(['prefix' => config('larascord.prefix'), 'middleware' => ['web']], function() {
    Route::get('/callback', [DiscordController::class, 'handle'])
        ->name('larascord.login');

    Route::redirect('/refresh-token', '/login')
        ->name('larascord.refresh_token');
});