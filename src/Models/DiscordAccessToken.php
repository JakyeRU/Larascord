<?php

namespace Jakyeru\Larascord\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordAccessToken extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['access_token', 'refresh_token', 'token_type', 'expires_in', 'expires_at', 'scope', 'discord_user_id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
    ];
}