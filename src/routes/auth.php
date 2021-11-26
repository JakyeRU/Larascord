<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DiscordController;
use Illuminate\Support\Facades\Route;

Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . env("DISCORD_CLIENT_ID") . '&redirect_uri=' . env("DISCORD_REDIRECT_URI") . '&response_type=code&scope=' . implode('%20', explode('&', env("DISCORD_SCOPE"))))
    ->name('login');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware('auth')
    ->name('password.confirm');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::group(['prefix' => 'larascord'], function() {
    Route::get('/callback', [DiscordController::class, 'handle'])
        ->name('larascord.login');

    Route::redirect('/refresh-token', '/login')
        ->name('larascord.refresh_token');
});