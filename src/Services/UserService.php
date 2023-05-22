<?php

namespace Jakyeru\Larascord\Services;

use App\Events\UserWasCreated;
use App\Events\UserWasUpdated;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class UserService
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
    public function getDiscordAccessToken(string $code): object
    {
        $this->tokenData['code'] = $code;

        $response = Http::asForm()->post($this->tokenURL, $this->tokenData);

        $response->throw();

        return json_decode($response->body());
    }

    /**
     * Authenticates the user with the access token and returns the user data.
     *
     * @throws RequestException
     */
    public function getDiscordUser(string $accessToken): object
    {
        $response = Http::withToken($accessToken)->get($this->baseApi . '/users/@me');

        $response->throw();

        return json_decode($response->body());
    }

    /**
     * Get the user's guilds.
     *
     * @throws RequestException
     */
    public function getDiscordUserGuilds(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get($this->baseApi . '/users/@me/guilds');

        $response->throw();

        return json_decode($response->body());
    }

    /**
     * Get the Guild Member object for a user.
     *
     * @throws RequestException
     */
    public function getDiscordGuildMember(string $accessToken, string $guildId, string $userId): object
    {
        $response = Http::withToken($accessToken, 'Bot')->get($this->baseApi . '/guilds/' . $guildId . '/members/' . $userId);

        $response->throw();

        return json_decode($response->body());
    }

    /**
     * Create or update a user in the database.
     */
    public function createOrUpdateUser(object $user, string $refresh_token): User
    {
        $user = User::updateOrCreate(
            [
                'id' => $user->id,
            ],
            [
                'username' => $user->username,
                'discriminator' => $user->discriminator,
                'email' => $user->email ?? NULL,
                'avatar' => $user->avatar ?: NULL,
                'verified' => $user->verified ?? FALSE,
                'locale' => $user->locale,
                'mfa_enabled' => $user->mfa_enabled,
                'refresh_token' => $refresh_token
            ]
        );

        if ($user->wasRecentlyCreated) {
            event(new UserWasCreated($user));
        } else {
            event(new UserWasUpdated($user));
        }

        return $user;
    }
}