<?php

namespace Jakyeru\Larascord\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Providers\RouteServiceProvider;

class DiscordController extends Controller
{
    protected string $tokenURL = "https://discord.com/api/oauth2/token";
    protected string $apiURLBase = "https://discord.com/api/users/@me";
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
     * @return \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        // Checking if the authorization code is present in the request.
        if ($request->missing('code')) {
            return redirect('/')->with('error', config('larascord.error_messages.missing_code', 'The authorization code is missing.'));
        }

        // Getting the access_token from the Discord API.
        try {
            $accessToken = $this->getDiscordAccessToken($request->get('code'));
        } catch (\Exception $e) {
            return redirect('/')->with('error', config('larascord.error_messages.invalid_code', 'The authorization code is invalid.'));
        }

        // Using the access_token to get the user's Discord ID.
        try {
            $user = $this->getDiscordUser($accessToken->access_token);
        } catch (\Exception $e) {
            return redirect('/')->with('error', config('larascord.error_messages.authorization_failed', 'The authorization failed.'));
        }

        // Making sure the user has an email.
        if (empty($user->email)) {
            return redirect('/')->with('error', config('larascord.error_messages.missing_email', 'Couldn\'t get your e-mail address. Make sure you are using the <strong>identify&email</strong> scopes.'));
        }

        // Making sure the current logged-in user's ID is matching the ID retrieved from the Discord API.
        if (Auth::check() && (Auth::id() !== $user->id)) {
            Auth::logout();
            return redirect('/')->with('error', config('larascord.error_messages.invalid_user', 'The user ID doesn\'t match the logged-in user.'));
        }

        // Confirming the session in case the user was redirected from the password.confirm middleware.
        if (Auth::check()) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        // Trying to create or update the user in the database.
        try {
            $user = $this->createOrUpdateUser($user, $accessToken->refresh_token);
        } catch (\Exception $e) {
            return redirect('/')->with('error', config('larascord.error_messages.database_error', 'There was an error with the database. Please try again later.'));
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
     * @param string $access_token
     * @return object
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function getDiscordUser(string $access_token): object
    {
        $response = Http::withToken($access_token)->get($this->apiURLBase);

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
        return User::updateOrCreate(
            [
                'id' => $user->id,
            ],
            [
                'username' => $user->username,
                'discriminator' => $user->discriminator,
                'email' => $user->email,
                'avatar' => $user->avatar ?: NULL,
                'verified' => $user->verified,
                'locale' => $user->locale,
                'mfa_enabled' => $user->mfa_enabled,
                'refresh_token' => $refresh_token
            ]
        );
    }
}