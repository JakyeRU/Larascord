<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordAccessToken extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'access_token',
        'refresh_token',
        'token_type',
        'expires_in',
        'expires_at',
        'scope',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'timestamp',
    ];
}