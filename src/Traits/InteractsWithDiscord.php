<?php

namespace Jakyeru\Larascord\Traits;

use App\Models\DiscordAccessToken;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait InteractsWithDiscord
{
    /**
     * Get the user's access token.
     */
    public function accessToken(): HasOne
    {
        return $this->hasOne(DiscordAccessToken::class);
    }
}