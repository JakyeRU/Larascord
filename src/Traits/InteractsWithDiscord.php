<?php

namespace Jakyeru\Larascord\Traits;

use App\Models\DiscordAccessToken;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Client\RequestException;
use Jakyeru\Larascord\Services\DiscordService;
use Jakyeru\Larascord\Types\AccessToken;

trait InteractsWithDiscord
{
    /**
     * Get the user's access token relationship.
     */
    public function accessToken(): HasOne
    {
        return $this->hasOne(DiscordAccessToken::class);
    }

    /**
     * Get the user's access token.
     */
    public function getAccessToken(): ?AccessToken
    {
        $accessToken = $this->accessToken()->first();

        if ($accessToken && $accessToken->expires_at->isPast()) {
            $this->refreshAccessToken();
        }

        return new AccessToken($accessToken);
    }
}