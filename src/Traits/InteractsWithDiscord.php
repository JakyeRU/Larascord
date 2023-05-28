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
            $accessToken = $this->refreshAccessToken();

            return $accessToken ? new AccessToken($accessToken) : null;
        }

        return new AccessToken($accessToken);
    }

    /**
     * Refresh the user's access token.
     */
    public function refreshAccessToken(): ?AccessToken
    {
        $accessToken = $this->accessToken()->first();
        
        if ($accessToken) {
            try {
                $response = (new DiscordService())->refreshAccessToken($accessToken->refresh_token);
            } catch (RequestException $e) {
                return null;
            }

            $accessToken->update([
                'access_token' => $response->access_token,
                'refresh_token' => $response->refresh_token,
                'expires_at' => $response->expires_at,
            ]);

            return new AccessToken($accessToken);
        }

        return null;
    }
}