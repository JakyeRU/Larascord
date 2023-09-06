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
    | Application Access Token
    |--------------------------------------------------------------------------
    |
    | This is the access token of your Discord application.
    |
    */

    'access_token' => env('LARASCORD_ACCESS_TOKEN', null),

    /*
    |--------------------------------------------------------------------------
    | Grant Type
    |--------------------------------------------------------------------------
    |
    | This is the grant type of your Discord application. It must be set to
    | "authorization_code".
    |
    */

    'grant_type' => env('LARASCORD_GRANT_TYPE', 'authorization_code'),

    /*
    |--------------------------------------------------------------------------
    | Redirect URI
    |--------------------------------------------------------------------------
    |
    | This is the URI that Discord will redirect to after the user authorizes
    | your application.
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
    | This is the prefix that Larascord will use for its routes. For example,
    | the prefix "larascord" will result in the route
    | "https://domain.com/larascord/login".
    |
    */

    'route_prefix' => env('LARASCORD_PREFIX', 'larascord'),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Prompt - "none" or "consent"
    |--------------------------------------------------------------------------
    |
    | The prompt controls how the authorization flow handles existing authorizations.
    | If a user has previously authorized your application with the requested scopes
    | and prompt is set to consent,it will request them to re-approve their
    | authorization. If set to none, it will skip the authorization screen
    | and redirect them back to your redirect URI without requesting
    | their authorization.
    |
    */

    'prompt' => 'none',

    /*
    |--------------------------------------------------------------------------
    | Restrict Access to Specific Guilds
    |--------------------------------------------------------------------------
    |
    | This option restricts access to the application to users who are members
    | of specific Discord guilds. Users who are not members of the specified
    | guilds will not be able to use the application.
    |
    */

    'guilds' => [],

    /*
    |--------------------------------------------------------------------------
    | Restrict Access to Specific Guilds - Strict Mode
    |--------------------------------------------------------------------------
    |
    | Enabling this option will require the user to be a member of ALL the
    | aforementioned guilds. If this option is disabled, the user will
    | only need to be a member of at least ONE of the guilds.
    |
    */

    'guilds_strict' => false,

    /*
    |--------------------------------------------------------------------------
    | Restrict Access to Specific Roles
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, the user will only be able to use the
    | application if they have at least one of the specified roles.
    |
    */

    // WARNING: This feature makes one request to the Discord API for each guild you specify. (Because you need to fetch the roles for each guild)

    'guild_roles' => [
        // 'guild_id' => [
        //     'role_id',
        //     'role_id',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Remember Me
    |--------------------------------------------------------------------------
    |
    | Whether or not to remember the user after they log in.
    |
    */

    'remember_me' => false,

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | These are the error messages that will be displayed to the user if there
    | is an error.
    |
    */

    'error_messages' => [
        'missing_code' => [
            'message' => 'The authorization code is missing.',
            'redirect' => '/'
        ],
        'invalid_code' => [
            'message' => 'The authorization code is invalid.',
            'redirect' => '/'
        ],
        'authorization_failed' => [
            'message' => 'The authorization failed.',
            'redirect' => '/'
        ],
        'missing_email' => [
            'message' => 'Couldn\'t get your e-mail address.',
            'redirect' => '/'
        ],
        'invalid_user' => [
            'message' => 'The user ID doesn\'t match the logged-in user.',
            'redirect' => '/'
        ],
        'database_error' => [
            'message' => 'There was an error with the database. Please try again later.',
            'redirect' => '/'
        ],
        'missing_guilds_scope' => [
            'message' => 'The "guilds" scope is required.',
            'redirect' => '/'
        ],
        'missing_guilds_members_read_scope' => [
            'message' => 'The "guilds" and "guilds.members.read" scopes are required.',
            'redirect' => '/'
        ],
        'authorization_failed_guilds' => [
            'message' => 'Couldn\'t get the servers you\'re in.',
            'redirect' => '/'
        ],
        'not_member_guild_only' => [
            'message' => 'You are not a member of the required guilds.',
            'redirect' => '/'
        ],
        'missing_access_token' => [
            'message' => 'The access token is missing.',
            'redirect' => '/'
        ],
        'authorization_failed_roles' => [
            'message' => 'Couldn\'t get the roles you have.',
            'redirect' => '/'
        ],
        'missing_role' => [
            'message' => 'You don\'t have the required roles.',
            'redirect' => '/'
        ],
        'revoke_token_failed' => [
            'message' => 'An error occurred while trying to revoke your access token.',
            'redirect' => '/'
        ],
        'user_deleted' => [
            'message' => 'Your account is deleted and you can\'t log in.',
            'redirect' => '/'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Success Messages
    |--------------------------------------------------------------------------
    |
    | These are the success messages that will be displayed to the user if there
    | is no error.
    |
    */

    'success_messages' => [
        'user_deleted' => [
            'message' => 'Your account has been deleted.',
            'redirect' => '/'
        ],
    ],

];
