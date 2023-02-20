<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application ID
    |--------------------------------------------------------------------------
    |
    | This is the ID of your Discord application.
    |
    */

    'client_id' => env('LARASCORD_CLIENT_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Application Secret
    |--------------------------------------------------------------------------
    |
    | This is the secret of your Discord application.
    |
    */

    'client_secret' => env('LARASCORD_CLIENT_SECRET', null),

    /*
    |--------------------------------------------------------------------------
    | Grant Type
    |--------------------------------------------------------------------------
    |
    | This is the grant type of your Discord application. It must be set to "authorization_code".
    |
    */

    'grant_type' => env('LARASCORD_GRANT_TYPE', 'authorization_code'),

    /*
    |--------------------------------------------------------------------------
    | Redirect URI
    |--------------------------------------------------------------------------
    |
    | This is the URI that Discord will redirect to after the user authorizes your application.
    |
    */

    'redirect_uri' => env('APP_URL', 'http://localhost:8000') . '/' . env('LARASCORD_PREFIX', 'larascord') . '/callback',

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | These are the OAuth2 scopes of your Discord application.
    |
    */

    'scopes' => env('LARASCORD_SCOPE', 'identify&email'),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the prefix that Larascord will use for its routes.
    | For example, the prefix "larascord" will result in the route "https://domain.com/larascord/login".
    |
    */

    'prefix' => env('LARASCORD_PREFIX', 'larascord'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Prompt - "none" or "consent"
    |--------------------------------------------------------------------------
    |
    | The prompt controls how the authorization flow handles existing authorizations.
    | If a user has previously authorized your application with the requested scopes and prompt is set to consent,
    | it will request them to re-approve their authorization.
    | If set to none, it will skip the authorization screen and redirect them back to your redirect URI without requesting their authorization.
    |
    */

    'prompt' => 'none',

    /*
    |--------------------------------------------------------------------------
    | Guild-Only Login
    |--------------------------------------------------------------------------
    |
    | This setting allows users to use the application only if they are a member of specific guilds.
    |
    */
    'guild_only' => false,
    'guilds' => [],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | These are the error messages that will be display to the user if there is an error.
    |
    */

    'error_messages' => [
        'missing_code' => 'The authorization code is missing.',
        'invalid_code' => 'The authorization code is invalid.',
        'authorization_failed' => 'The authorization failed.',
        'missing_email' => 'Couldn\'t get your e-mail address.',
        'invalid_user' => 'The user ID doesn\'t match the logged-in user.',
        'database_error' => 'There was an error with the database. Please try again later.',
        'missing_guilds_scope' => 'The "guilds" scope is required.',
        'authorization_failed_guilds' => 'Couldn\'t get the servers you\'re in.',
        'not_member_guild_only' => 'You are not allowed to login.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Success Messages
    |--------------------------------------------------------------------------
    |
    | These are the success messages that will be display to the user if there is no error.
    |
    */

    'success_messages' => [
        'user_deleted' => 'Your account has been deleted.',
    ],

];
