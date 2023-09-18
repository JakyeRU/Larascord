<?php

namespace Jakyeru\Larascord\Traits;

use Jakyeru\Larascord\Models\DiscordAccessToken;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Jakyeru\Larascord\Services\DiscordService;
use Jakyeru\Larascord\Types\AccessToken;
use Jakyeru\Larascord\Types\Channel;
use Jakyeru\Larascord\Types\Guild;
use Jakyeru\Larascord\Types\GuildMember;
use Jakyeru\Larascord\Types\GuildPreview;

trait InteractsWithDiscord
{
    /**
     * The Discord CDN base URL.
     *
     * @var string
     */
    protected string $cdn = "https://cdn.discordapp.com";

    /**
     * Get the user's tag attribute.
     *
     * @return string
     * @throws Exception
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
     *
     * @return HasOne
     * @throws Exception
     */
    public function accessToken(): HasOne
    {
        return $this->hasOne(DiscordAccessToken::class);
    }

    /**
     * Get the user's access token.
     *
     * @return AccessToken|null
     * @throws RequestException
     * @throws Exception
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
     *
     * @return AccessToken|null
     * @throws RequestException
     * @throws Exception
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
     *
     * @param array $options
     * @return string
     * @throws Exception
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
     * @param bool $withCounts
     * @return Collection
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
     * Get a specific guild from the user's guilds.
     *
     * @param string $guildId
     * @param bool $withCounts
     * @return Collection
     * @throws RequestException
     * @throws Exception
     */
    public function getGuild(string $guildId, bool $withCounts = false): Collection
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        $request = (new DiscordService())->getGuild($accessToken, $guildId, $withCounts);

        return collect($request);
    }

    /**
     * Get all channels within a guild that the access / bot token has access to.
     *
     * @param string $guildId
     * @return Collection
     * @throws RequestException
     */
    public function getGuildChannels(string $guildId): Collection
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        $response = (new DiscordService())->getGuildChannels($accessToken, $guildId);

        return collect($response);
    }

    /**
     * @throws RequestException
     * @throws Exception
     */
    public function getGuildRoles(string $guildId): Collection
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        $response = (new DiscordService())->getGuildRoles($accessToken, $guildId);

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
     * Get a guilds preview information
     *
     * @param string $guildId
     * @return GuildPreview
     * @throws Exception
     */
    public function getGuildPreview(string $guildId): GuildPreview
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('The access token is invalid.');
        }

        return (new DiscordService())->getGuildPreview($accessToken, $guildId);
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
