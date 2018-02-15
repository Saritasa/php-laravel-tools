<?php

namespace Saritasa\LaravelTools;

use Doctrine\DBAL\Connection;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelTools\Commands\FormRequestsScaffoldCommand;
use Saritasa\LaravelTools\Database\DatabaseConnectionManager;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\Factories\FormRequestFactory;


class LaravelToolsServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/laravel_tools.php' =>
                        $this->app->make('path.config') . DIRECTORY_SEPARATOR . 'laravel_tools.php',
                ],
                'laravel_tools'
            );

            $this->mergeConfigFrom(__DIR__ . '/../config/laravel_tools.php', 'laravel_tools');

            $this->commands([
                FormRequestsScaffoldCommand::class,
            ]);

            $this->app->when(SchemaReader::class)
                ->needs(Connection::class)
                ->give(function (Container $app) {
                    return $app->make(DatabaseConnectionManager::class)->getConnection();
                });
        }
    }

    /**
     * Returns array vith provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [FormRequestFactory::class];
    }
}
