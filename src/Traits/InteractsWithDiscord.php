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
     * Get the user's access token.
     */
    public function accessToken(): HasOne
    {
        return $this->hasOne(DiscordAccessToken::class);
    }

    /**
     * @throws RequestException
     */
    public function getGuilds(): AccessToken
    {
        $accessToken = (new DiscordService())->getAccessTokenFromRefreshToken($this);

        $this->update([
            'refresh_token' => $accessToken->refresh_token,
        ]);

        return $accessToken;
    }
}