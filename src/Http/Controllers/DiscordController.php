<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;

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
        $this->tokenData['client_id'] = env('DISCORD_CLIENT_ID');
        $this->tokenData['client_secret'] = env('DISCORD_CLIENT_SECRET');
        $this->tokenData['grant_type'] = env('DISCORD_GRANT_TYPE');
        $this->tokenData['redirect_uri'] = env('DISCORD_REDIRECT_URI');
        $this->tokenData['scope'] = env('DISCORD_SCOPE');
    }

    /**
     * Handles the Discord OAuth2 login.
     *
     * @param Request $request
     * @return \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        if ($request->missing('code')) return redirect('/')->with('error', 'The code is missing.');

        try {
            $accessToken = $this->getDiscordAccessToken($request->get('code'));
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'There was an error while trying to get the access token.');
        }

        try {
            $user = $this->getDiscordUser($accessToken->access_token);
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'There was an error while trying to get the user data.');
        }

        if (Auth::check()) {
            if (Auth::id() !== $user->id) {
                Auth::logout();
                return redirect('/')->with('error', 'The user ID does not match the logged in user.');
            }

            $request->session()->put('auth.password_confirmed_at', time());

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        try {
            $user = $this->createOrUpdateUser($user, $accessToken->refresh_token);
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'There was an error while trying to create or update the user.');
        }

        Auth::login($user);

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
        if (!isset($user->id)) {
            throw new \Exception('Couldn\'t get your e-mail address. Make sure you are using the <strong>identify&email</strong> scope.');
        }

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