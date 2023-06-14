<?php

namespace Jakyeru\Larascord\Tests\Unit;

use Jakyeru\Larascord\LarascordServiceProvider;
use Orchestra\Testbench\TestCase;

class RoutesTest extends TestCase
{
    public function setUp():void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LarascordServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('larascord.client_id', env('LARASCORD_CLIENT_ID'));
        $app['config']->set('larascord.client_secret', env('LARASCORD_CLIENT_SECRET'));
        $app['config']->set('larascord.redirect_uri', env('APP_URL', 'http://localhost:8000') . '/' . env('LARASCORD_PREFIX', 'larascord') . '/callback',);
        $app['config']->set('larascord.scopes', env('LARASCORD_SCOPE'));
        $app['config']->set('larascord.route_prefix', 'larascord');
        $app['config']->set('larascord.guilds', []);
        $app['config']->set('larascord.guild_roles', []);
    }

    public function test_login_route_redirect()
    {
        $request = $this->get('/login');

        $request->assertStatus(302);

        $request->assertHeader('Location', 'https://discord.com/oauth2/authorize?client_id=0000000000000000&redirect_uri=http://localhost:8000/larascord/callback&response_type=code&scope=identify%20email&prompt=none');


        $request = $this->get(config('larascord.route_prefix') . '/refresh-token');

        $request->assertStatus(302);

        $request->assertHeader('Location', config('app.url') . '/login');
    }

    public function test_callback_route()
    {
        $request = $this->get(config('larascord.route_prefix') . '/callback');

        $request->assertStatus(302);

        $request->assertSessionHasErrors([
            'code' => 'The code field is required.'
        ]);

        $request = $this->get(config('larascord.route_prefix') . '/callback?code=0000000000000000');

        $request->assertStatus(302);

        $request->assertSessionHas('error', 'An error occurred while trying to log you in.');
    }
}