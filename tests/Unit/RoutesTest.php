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
    }

    public function test_login_route_redirect()
    {
        $request = $this->get('/login');

        $request->assertStatus(302);

        $request->assertHeader('Location', 'https://discord.com/oauth2/authorize?client_id=0000000000000000&redirect_uri=http://localhost:8000/larascord/callback&response_type=code&scope=identify%20email&prompt=none');


        $request = $this->get('/refresh-token');

        $request->assertStatus(302);

        $request->assertHeader('Location', '/login');
    }

    public function test_callback_route()
    {
        $request = $this->get('/callback');

        $request->assertStatus(302);

        $request->assertSessionHas('error', 'The authorization code is missing.');


        $request = $this->get('/callback?code=0000000000000000');

        $request->assertStatus(302);

        $request->assertSessionHas('error', 'The authorization code is invalid.');
    }
}