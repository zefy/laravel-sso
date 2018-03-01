<?php

namespace Zefy\LaravelSSO;

use Illuminate\Support\ServiceProvider;

class SSOServiceProvider extends ServiceProvider
{
    /**
     * Configuration file name.
     *
     * @var string
     */
    protected $configFileName = 'laravel-sso.php';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig(__DIR__ . '/../config/' . $this->configFileName);

        // If this is server, load routes which is required for the server.
        if (config('laravel-sso.type') == 'server') {
            $this->loadRoutesFrom(__DIR__.'/Routes/server.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateBroker::class,
                DeleteBroker::class,
                ListBrokers::class,
            ]);
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Zefy\LaravelSSO\Controllers\ServerController');
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return config_path($this->configFileName);
    }

    /**
     * Publish the config file
     *
     * @param string $configPath
     */
    protected function publishConfig(string $configPath)
    {
        $this->publishes([$configPath => $this->getConfigPath()]);
    }
}
