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

        $this->registerRoutes();
    }

    protected function registerCommands()
    {
        $this->commands([
            Console\Commands\InstallCommand::class,
            Console\Commands\PublishCommand::class,
        ]);
    }

    protected function registerConfiguration()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('larascord.php'),
        ], 'config');
    }

    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/larascord.php');
    }
}