<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/login', 'https://discord.com/oauth2/authorize?client_id=' . env("DISCORD_CLIENT_ID") . '&redirect_uri=' . env("DISCORD_REDIRECT_URI") . '&response_type=code&scope=' . env("DISCORD_SCOPE"))->name('login');