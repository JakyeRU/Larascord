<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . env("DISCORD_CLIENT_ID") . '&redirect_uri=' . env("DISCORD_REDIRECT_URI") . '&response_type=code&scope=' . env("DISCORD_SCOPE"))->name('login');

Route::group(['prefix' => 'larascord'], function() {
    Route::get('/callback', [\App\Http\Controllers\DiscordController::class, 'login'])->name('larascord.login');
    Route::post('/logout', [\App\Http\Controllers\DiscordController::class, 'logout'])->name('larascord.logout');
});