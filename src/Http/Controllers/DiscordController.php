<?php

namespace Jakyeru\Larascord\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use Jakyeru\Larascord\Http\Requests\StoreUserRequest;
use Jakyeru\Larascord\Services\DiscordService;

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
        $discordService = new DiscordService();

        // Getting the accessToken from the Discord API.
        try {
            $accessToken = (new DiscordService())->getAccessTokenFromCode($request->get('code'));
        } catch (\Exception $e) {
            return $this->throwError('invalid_code', $e);
        }

        // Get the user from the Discord API.
        try {
            $user = (new DiscordService())->getCurrentUser($accessToken);
        } catch (\Exception $e) {
            return $this->throwError('authorization_failed', $e);
        }

        // Verifying if the user is in any of "larascord.guilds" if there are any guilds specified in "larascord.guilds"
        if (count(config('larascord.guilds'))) {
            try {
                $guilds = $discordService->getDiscordUserGuilds($accessToken->access_token);

                if (!$discordService->isUserInGuilds($guilds)) {
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

        if (auth()->check()) {
            // Making sure the current logged-in user's ID is matching the ID retrieved from the Discord API.
            if (auth()->id() !== (int)$user->id) {
                auth()->logout();
                return $this->throwError('invalid_user');
            }

            // Confirming the session in case the user was redirected from the password.confirm middleware.
            $request->session()->put('auth.password_confirmed_at', time());
        }

        // Trying to create or update the user in the database.
        try {
            $user = $discordService->createOrUpdateUser($user, $accessToken->access_token);
        } catch (\Exception $e) {
            return $this->throwError('database_error', $e);
        }

        // Verifying if the user has the required roles if "larascord.roles" is set.
        if (count(config('larascord.guild_roles'))) {
            // Verifying if an access token is set.
            if (!in_array('guilds', explode('&', config('larascord.scopes'))) || !in_array('guilds.members.read', explode('&', config('larascord.scopes')))) {
                return $this->throwError('missing_guilds_members_read_scope');
            }

            // Verifying if the user has the required roles.
            try {
                foreach (config('larascord.guild_roles') as $guild => $roles) {
                    $guildMember = $discordService->getDiscordGuildMember($accessToken->access_token, $guild);

                    if (!$discordService->hasRoleInGuild($user, $guildMember, $guild, $roles)) {
                        return $this->throwError('missing_role');
                    }
                }
            } catch (\Exception $e) {
                return $this->throwError('authorization_failed_roles', $e);
            }
        }

        // Authenticating the user if the user is not logged in.
        if (!auth()->check()) {
            auth()->login($user, config('larascord.remember_me', false));
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
    public function destroy(): RedirectResponse | JsonResponse
    {
        // Revoking the OAuth2 access token.
        try {
            (new DiscordService())->revokeAccessToken(auth()->user()->refresh_token);
        } catch (\Exception $e) {
            return $this->throwError('revoke_token_failed', $e);
        }

        // Deleting the user from the database.
        auth()->user()->delete();

        // Showing the success message.
        if (config('larascord.success_messages.user_deleted.redirect')) {
            return redirect(config('larascord.success_messages.user_deleted.redirect'))->with('success', config('larascord.success_messages.user_deleted.message', 'Your account has been deleted.'));
        } else {
            return redirect('/')->with('success', config('larascord.success_messages.user_deleted', 'Your account has been deleted.'));
        }
    }
}