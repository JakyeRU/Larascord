<?php

namespace Jakyeru\Larascord;

use Illuminate\Support\ServiceProvider;

class LarascordServiceProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerConfiguration();
        }
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerConfiguration();
        }
    }

    protected function registerCommands()
    {
        $this->commands([
            Console\Commands\InstallCommand::class,
        ]);
    }

    protected function registerConfiguration()
    {
        $this->publishes([
            __DIR__.'./config/config.php' => config_path('larascord.php'),
        ], 'config');
    }
}