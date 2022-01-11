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

    'client_id' => env('DISCORD_CLIENT_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Application Secret
    |--------------------------------------------------------------------------
    |
    | This is the secret of your Discord application.
    |
    */

    'client_secret' => env('DISCORD_CLIENT_SECRET', null),

    /*
    |--------------------------------------------------------------------------
    | Grant Type
    |--------------------------------------------------------------------------
    |
    | This is the grant type of your Discord application. It must be set to "authorization_code".
    |
    */

    'grant_type' => env('DISCORD_GRANT_TYPE', 'authorization_code'),

    /*
    |--------------------------------------------------------------------------
    | Redirect URI
    |--------------------------------------------------------------------------
    |
    | This is the URI that Discord will redirect to after the user authorizes your application.
    |
    */

    'redirect_uri' => env('APP_URL', 'http://localhost:8000') . '/' . config('larascord.prefix') . '/callback',

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | These are the OAuth2 scopes of your Discord application.
    |
    */

    'scopes' => 'identify&email',

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the prefix that Larascord will use for its routes.
    | For example, the prefix "larascord" will result in the route "https://domain.com/larascord/login".
    |
    */

    'prefix' => 'larascord',

];