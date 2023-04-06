<?php

namespace Jakyeru\Larascord\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Events\UserWasCreated;
use App\Events\UserWasUpdated;

class DiscordController extends Controller
{
    protected string $tokenURL = "https://discord.com/api/oauth2/token";
    protected string $baseApi = "https://discord.com/api";
    protected array $tokenData = [
        "client_id" => NULL,
        "client_secret" => NULL,
        "grant_type" => "authorization_code",
        "code" => NULL,
        "redirect_uri" => NULL,
        "scope" => null
    ];

    /**
     * Sets the required data for the token request.
     *
     * @return void
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
     * Handles the Discord OAuth2 login.
     *
     * @param Request $request
//     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)//: \Illuminate\Http\JsonResponse
    {
        // Checking if the authorization code is present in the request.
        if ($request->missing('code')) {
            return $this->throwError('missing_code');
        }

        // Making sure the "guilds" scope was added to .env if "guild_only" is set to true.
        if (!in_array('guilds', explode('&', config('larascord.scopes'))) && config('larascord.guild_only')) {
            return $this->throwError('missing_guilds_scope');
        }

        // Getting the accessToken from the Discord API.
        try {
            $accessToken = $this->getDiscordAccessToken($request->get('code'));
        } catch (\Exception $e) {
            return $this->throwError('invalid_code', $e);
        }

        // Using the accessToken to get the user's Discord ID.
        try {
            $user = $this->getDiscordUser($accessToken->access_token);
        } catch (\Exception $e) {
            return $this->throwError('authorization_failed', $e);
        }

        // Verifying if the user is in any of "larascord.guilds" if "larascord.guild_only" is true.
        if (config('larascord.guild_only')) {
            try {
                $guilds = $this->getUserGuilds($accessToken->access_token);

                $isMember = call_user_func(function () use ($guilds) {
                    foreach ($guilds as $guild) {
                        if (in_array($guild->id, config('larascord.guilds'))) {
                            return true;
                        }
                    }
                    return false;
                });

                if (!$isMember) {
                    return $this->throwError('not_member_guild_only');
                }

            } catch (\Exception $e) {
                return $this->throwError('authorization_failed_guilds', $e);
            }
        }

        // Making sure the user has an email if the email scope is set.
        if (in_array('email', explode('&', config('larascord.scopes')))) {
            if (empty($user->email)) {
                return $this->throwError('missing_email');
            }
        }

        // Making sure the current logged-in user's ID is matching the ID retrieved from the Discord API.
        if (Auth::check() && (Auth::id() !== $user->id)) {
            Auth::logout();
            return $this->throwError('invalid_user');
        }

        // Confirming the session in case the user was redirected from the password.confirm middleware.
        if (Auth::check()) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        // Trying to create or update the user in the database.
        try {
            $user = $this->createOrUpdateUser($user, $accessToken->refresh_token);
        } catch (\Exception $e) {
            return $this->throwError('database_error', $e);
        }

        // Authenticating the user if the user is not logged in.
        if (!Auth::check()) {
            Auth::login($user);
        }

        // Redirecting the user to the intended page or to the home page.
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Handles the Discord OAuth2 callback.
     *
     * @param string $code
     * @return object
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function getDiscordAccessToken(string $code): object
    {
        $this->tokenData['code'] = $code;

        $response = Http::asForm()->post($this->tokenURL, $this->tokenData);

        $response->throw();

        return json_decode($response->body());
    }

    /**
     * Handles the Discord OAuth2 login.
     *
     * @param string $accessToken
     * @return object
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function getDiscordUser(string $accessToken): object
    {
        $response = Http::withToken($accessToken)->get($this->baseApi . '/users/@me');

        $response->throw();

        return json_decode($response->body());
    }

    private function getUserGuilds($accessToken)
    {
        $response = Http::withToken($accessToken)->get($this->baseApi . '/users/@me/guilds');

        $response->throw();

        return json_decode($response->body());
    }

    /**
     * Handles the retrieval of the user's roles in a guild.
     *
     * @param string $guildId
     * @param string $accessToken
     * @return mixed
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function getGuildMemberInfo(string $guildId, string $userId, string $accessToken)
    {
        $response = Http::withToken($accessToken)->get($this->baseApi . '/guilds/' . $guildId . '/members/' . $userId);

        $response->throw();

        return json_decode($response->body());
    }

    /**
     * Handles the creation or update of the user.
     *
     * @param object $user
     * @param string $refresh_token
     * @return User
     * @throws \Exception
     */
    private function createOrUpdateUser(object $user, string $refresh_token): User
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

    /**
     * Handles the throwing of an error.
     */
    private function throwError(string $message, \Exception $exception = NULL): RedirectResponse | JsonResponse
    {
        if (app()->hasDebugModeEnabled()) {
            return response()->json([
                'larascord_message' => config('larascord.error_messages.' . $message),
                'message' => $exception?->getMessage(),
                'code' => $exception?->getCode()
            ]);
        } else {
            return redirect('/')->with('error', config('larascord.error_messages.' . $message));
        }
    }

    /**
     * Handles the deletion of the user.
     */
    public function destroy()
    {
        $user = Auth::user();

        $user->delete();

        return redirect('/')->with('success', config('larascord.success_messages.user_deleted', 'Your account has been deleted.'));
    }
}