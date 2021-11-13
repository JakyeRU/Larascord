<?php

namespace Jakyeru\Larascord;

use Illuminate\Support\ServiceProvider;

class LarascordServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerCommands();
    }

    public function boot()
    {
//        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
            ]);
        }
    }
}