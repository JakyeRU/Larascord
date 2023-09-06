<?php

namespace Jakyeru\Larascord\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Jakyeru\Larascord\Types\AccessToken;
use Jakyeru\Larascord\Types\GuildMember;

class DiscordService
{
    /**
     * The Discord OAuth2 token URL.
     */
    protected string $tokenURL = "https://discord.com/api/oauth2/token";

    /**
     * The Discord API base URL.
     */
    protected string $baseApi = "https://discord.com/api";
    /**
     * The required data for the token request.
     */
    protected array $tokenData = [
        "client_id" => NULL,
        "client_secret" => NULL,
        "grant_type" => "authorization_code",
        "code" => NULL,
        "redirect_uri" => NULL,
        "scope" => null
    ];

    /**
     * UserService constructor.
     */
    public function __construct()
    {
        $this->tokenData['client_id'] = config('larascord.client_id');
        $this->tokenData['client_secret'] = config('larascord.client_secret');
        $this->tokenData['grant_type'] = config('larascord.grant_type');
        $this->tokenData['redirect_uri'] = config('larascord.redirect_uri');
        $this->tokenData['scope'] = config('larascord.scopes');
    }

    /**
     * Handles the Discord OAuth2 callback and returns the access token.
     *
     * @throws RequestException
     */
    public function getAccessTokenFromCode(string $code): AccessToken
    {
        $this->tokenData['code'] = $code;

        $response = Http::asForm()->post($this->tokenURL, $this->tokenData);

        $response->throw();

        return new AccessToken(json_decode($response->body()));
    }

    /**
     * Get access token from refresh token.
     *
     * @throws RequestException
     */
    public function refreshAccessToken(string $refreshToken): AccessToken
    {
        $response = Http::asForm()->post($this->tokenURL, [
            'client_id' => config('larascord.client_id'),
            'client_secret' => config('larascord.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        $response->throw();

        return new AccessToken(json_decode($response->body()));
    }

    /**
     * Authenticates the user with the access token and returns the user data.
     *
     * @throws RequestException
     */
    public function getCurrentUser(AccessToken $accessToken): \Jakyeru\Larascord\Types\User
    {
        $response = Http::withToken($accessToken->access_token)->get($this->baseApi . '/users/@me');

        $response->throw();

        return new \Jakyeru\Larascord\Types\User(json_decode($response->body()));
    }

    /**
     * Get the user's guilds.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function getCurrentUserGuilds(AccessToken $accessToken, bool $withCounts = false): array
    {
        if (!$accessToken->hasScope('guilds')) throw new Exception(config('larascord.error_messages.missing_guilds_scope.message'));

        $endpoint = '/users/@me/guilds';

        if ($withCounts) {
            $endpoint .= '?with_counts=true';
        }

        $response = Http::withToken($accessToken->access_token, $accessToken->token_type)->get($this->baseApi . $endpoint);

        $response->throw();

        return array_map(function ($guild) {
            return new \Jakyeru\Larascord\Types\Guild($guild);
        }, json_decode($response->body()));
    }

    /**
     * Get the Guild Member object for a user.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function getGuildMember(AccessToken $accessToken, string $guildId): GuildMember
    {
        if (!$accessToken->hasScopes(['guilds', 'guilds.members.read'])) throw new Exception(config('larascord.error_messages.missing_guilds_members_read_scope.message'));

        $response = Http::withToken($accessToken->access_token, $accessToken->token_type)->get($this->baseApi . '/users/@me/guilds/' . $guildId . '/member');

        $response->throw();

        return new GuildMember(json_decode($response->body()));
    }

    /**
     * Get the User's connections.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function getCurrentUserConnections(AccessToken $accessToken): array
    {
        if (!$accessToken->hasScope('connections')) throw new Exception('The "connections" scope is required.');

        $response = Http::withToken($accessToken->access_token, $accessToken->token_type)->get($this->baseApi . '/users/@me/connections');

        $response->throw();

        return array_map(function ($connection) {
            return new \Jakyeru\Larascord\Types\Connection($connection);
        }, json_decode($response->body()));
    }

    /**
     * Join a guild.
     *
     * @throws RequestException
     * @throws Exception
     */
    public function joinGuild(AccessToken $accessToken, User $user, string $guildId, array $options = []): GuildMember
    {
        if (!config('larascord.access_token')) throw new Exception(config('larascord.error_messages.missing_access_token.message'));
        if (!$accessToken->hasScope('guilds.join')) throw new Exception('The "guilds" and "guilds.join" scopes are required.');

        $response = Http::withToken(config('larascord.access_token'), 'Bot')->put($this->baseApi . '/guilds/' . $guildId . '/members/' . $user->id, array_merge([
            'access_token' => $accessToken->access_token,
        ], $options));

        $response->throw();

        if ($response->status() === 204) return throw new Exception('User is already in the guild.');

        return new GuildMember(json_decode($response->body()));
    }

    /**
     * Create or update a user in the database.
     *
     * @throws Exception
     */
    public function createOrUpdateUser(\Jakyeru\Larascord\Types\User $user): User
    {
        if (!$user->getAccessToken()) {
            throw new Exception('User access token is missing.');
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            return User::withTrashed()->updateOrCreate(
                [
                    'id' => $user->id,
                ],
                $user->toArray(),
            );
        }

        return User::updateOrCreate(
            [
                'id' => $user->id,
            ],
            $user->toArray(),
        );
    }

    /**
     * Verify if the user is in the specified guild(s).
     */
    public function isUserInGuilds(array $guilds): bool
    {
        // Verify if the user is in all the specified guilds if strict mode is enabled.
        if (config('larascord.guilds_strict')) {
            return empty(array_diff(config('larascord.guilds'), array_column($guilds, 'id')));
        }

        // Verify if the user is in any of the specified guilds if strict mode is disabled.
        return !empty(array_intersect(config('larascord.guilds'), array_column($guilds, 'id')));
    }

    /**
     * Verify if the user has the specified role(s) in the specified guild.
     */
    public function hasRoleInGuild(GuildMember $guildMember, array $roles): bool
    {
        // Verify if the user has any of the specified roles.
        return !empty(array_intersect($roles, $guildMember->roles));
    }

    /**
     * Revoke the user's access token.
     *
     * @throws RequestException
     */
    public function revokeAccessToken(string $accessToken): object
    {
        $response = Http::asForm()->post($this->tokenURL . '/revoke', [
            'token' => $accessToken,
            'client_id' => config('larascord.client_id'),
            'client_secret' => config('larascord.client_secret'),
        ]);

        $response->throw();

        return json_decode($response->body());
    }
}
