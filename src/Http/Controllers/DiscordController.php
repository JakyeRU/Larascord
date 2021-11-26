<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \GuzzleHttp;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Validation\ValidationException;

// TODO: Refactor this file.

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
     * Handle the login request.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request)
    {
        if ($request->missing('code') && $request->missing('access_token')) return redirect()->guest('/')->with('error', 'The code or access token is missing.');
        if (Auth::check()) return redirect()->guest('/')->with('error', 'You are already logged in.');

        if ($request->has('code')) {
            $this->tokenData['code'] = $request->code;
        }

        $client = new GuzzleHttp\Client();

        try {
            $accessTokenData = $client->post($this->tokenURL, ["form_params" => $this->tokenData]);
            $accessTokenData = json_decode($accessTokenData->getBody());
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            return redirect()->guest('/')->with('error', 'Couldn\'t get the access token from Discord.');
        };

        $userData = Http::withToken($accessTokenData->access_token)->get($this->apiURLBase);
        if ($userData->clientError() || $userData->serverError()) return redirect()->guest('/')->with('error', 'Couldn\'t get the user data from Discord.');

        $userData = json_decode($userData->body());
        if (!isset($userData->email)) return redirect()->guest('/')->with('error', 'Couldn\'t get your e-mail address. Make sure you are using the <strong>identify&email</strong> scope.');

        $user = User::updateOrCreate(
            [
                'id' => $userData->id,
            ],
            [
                'username' => $userData->username,
                'discriminator' => $userData->discriminator,
                'email' => $userData->email,
                'avatar' => $userData->avatar ?: NULL,
                'verified' => $userData->verified,
                'locale' => $userData->locale,
                'mfa_enabled' => $userData->mfa_enabled,
                'refresh_token' => $accessTokenData->refresh_token
            ]
        );

        Auth::login($user);

        return redirect('/');
    }

    /**
     * Handle the confirmation request.
     */
    public function refresh_token(Request $request)
    {
//        if ($userData->id !== Auth::id()) {
//            throw ValidationException::withMessages([
//                'password' => __('auth.password'),
//            ]);
//        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}