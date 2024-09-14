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
     * The Discord CDN base URL.
     */
    protected string $cdn = "https://cdn.discordapp.com";

    /**
     * Get the user's tag attribute.
     */
    public function getTagAttribute(): string
    {
        if ($this->discriminator != 0) {
            return $this->username . '#' . $this->discriminator;
        }

        if (!$this->global_name) {
            return $this->username;
        }

        if ($this->username == $this->global_name) {
            return $this->username;
        }

        return $this->username . ' (' . $this->global_name . ')';
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
    public function getAvatar(array $options = []): string
    {
        $extension = $options['extension'] ?? 'png';
        $size = $options['size'] ?? 128;
        $color = $options['color'] ?? 0;

        if ($this->avatar) {
            return $this->cdn . '/avatars/' . $this->id . '/' . $this->avatar . '.' . $extension . ($size ? '?size=' . $size : '');
        }


        return $this->cdn . '/embed/avatars/' . $color . '.png';
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
