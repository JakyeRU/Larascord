<?php

namespace Jakyeru\Larascord\Traits;

use Jakyeru\Larascord\Models\DiscordAccessToken;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Jakyeru\Larascord\Services\DiscordService;
use Jakyeru\Larascord\Types\AccessToken;
use Jakyeru\Larascord\Types\GuildMember;

trait InteractsWithDiscord
{
    /**
     * Get the user's tag attribute.
     */
    public function getTagAttribute(): string
    {
        if ($this->global_name) {
            return $this->global_name;
        }

        return $this->username . '#' . $this->discriminator;
    }

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

    /**
    * Get the user's Avatar url
    */
    public function getAvatar(?string $extension = 'png', ?int $size = null, ?int $color = null): string
    {
        $baseUrl = 'https://cdn.discordapp.com/';
        $sizeParam = $size ? "?size={$size}" : '';
        $colorParam = $color ?? rand(0, 5);

        if ($this->avatar) {
            return "{$baseUrl}avatars/{$this->id}/{$this->avatar}.{$extension}{$sizeParam}";
        }

        return "{$baseUrl}embed/avatars/{$colorParam}.png";
    }

    /**
     * Get the user's guilds.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function getGuilds(bool $withCounts = false): Collection
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        $response = (new DiscordService())->getCurrentUserGuilds($accessToken, $withCounts);

        return collect($response);
    }

    /**
     * Get the user's guild member.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function getGuildMember(string $guildId): GuildMember|null
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        $response = (new DiscordService())->getGuildMember($accessToken, $guildId);

        return new GuildMember($response);
    }

    /**
     * Join a guild.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function joinGuild(string $guildId, array $options = []): GuildMember|null
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        return (new DiscordService())->joinGuild($accessToken, $this, $guildId, $options);
    }

    /**
     * Get the user's connections.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function getConnections(): Collection
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        $response = (new DiscordService())->getCurrentUserConnections($accessToken);

        return collect($response);
    }
}
