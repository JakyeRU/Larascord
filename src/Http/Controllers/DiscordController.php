<?php

namespace Jakyeru\Larascord\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Jakyeru\Larascord\Http\Requests\StoreUserRequest;
use Jakyeru\Larascord\Services\UserService;

class DiscordController extends Controller
{
    /**
     * Handles the Discord OAuth2 login.
     */
    public function handle(StoreUserRequest $request): RedirectResponse | JsonResponse
    {
        // Making sure the "guilds" scope was added to .env if there are any guilds specified in "larascord.guilds".
        if (count(config('larascord.guilds'))) {
            if (!in_array('guilds', explode('&', config('larascord.scopes')))) {
                return $this->throwError('missing_guilds_scope');
            }
        }

        // Creating a new instance of the UserService.
        $userService = new UserService();

        // Getting the accessToken from the Discord API.
        try {
            $accessToken = $userService->getDiscordAccessToken($request->get('code'));
        } catch (\Exception $e) {
            return $this->throwError('invalid_code', $e);
        }

        // Get the user from the Discord API.
        try {
            $user = $userService->getDiscordUser($accessToken->access_token);
        } catch (\Exception $e) {
            return $this->throwError('authorization_failed', $e);
        }

        // Verifying if the user is in any of "larascord.guilds" if there are any guilds specified in "larascord.guilds"
        if (count(config('larascord.guilds'))) {
            try {
                $guilds = $userService->getDiscordUserGuilds($accessToken->access_token);

                if (!$userService->isUserInGuilds($guilds)) {
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
            $user = $userService->createOrUpdateUser($user, $accessToken->access_token);
        } catch (\Exception $e) {
            return $this->throwError('database_error', $e);
        }

        // Verifying if the user has the required roles if "larascord.roles" is set.
        if (count(config('larascord.guild_roles'))) {
            // Verifying if an access token is set.
            if (!config('larascord.access_token')) {
                return $this->throwError('missing_access_token');
            }

            // Verifying if the user has the required roles.
            try {
                foreach (config('larascord.guild_roles') as $guild => $roles) {
                    $guildMember = $this->getGuildMemberInfo($guild, $user->id, config('larascord.access_token'));

                    // Updating the user's roles in the database.
                    $updatedRoles = $user->roles;
                    $updatedRoles[$guild] = $guildMember->roles;
                    $user->roles = $updatedRoles;
                    $user->save();

                    $hasRole = call_user_func(function () use ($guildMember, $roles) {
                        foreach ($guildMember->roles as $role) {
                            if (in_array($role, $roles)) {
                                return true;
                            }
                        }

                        return false;
                    });

                    if (!$hasRole) {
                        return $this->throwError('missing_role');
                    }
                }
            } catch (\Exception $e) {
                return $this->throwError('authorization_failed_roles', $e);
            }
        }

        // Authenticating the user if the user is not logged in.
        if (!Auth::check()) {
            Auth::login($user);
        }

        // Redirecting the user to the intended page or to the home page.
        return redirect()->intended(RouteServiceProvider::HOME);
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
            if (config('larascord.error_messages.' . $message . '.redirect')) {
                return redirect(config('larascord.error_messages.' . $message . '.redirect'))->with('error', config('larascord.error_messages.' . $message . '.message', 'An error occurred while trying to log you in.'));
            } else {
                return redirect('/')->with('error', config('larascord.error_messages.' . $message, 'An error occurred while trying to log you in.'));
            }
        }
    }

    /**
     * Handles the deletion of the user.
     */
    public function destroy(): RedirectResponse
    {
        auth()->user()->delete();

        return redirect('/')->with('success', config('larascord.success_messages.user_deleted', 'Your account has been deleted.'));
    }
}