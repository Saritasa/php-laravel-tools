<?php

namespace Saritasa\LaravelTools;

use Doctrine\DBAL\Connection;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelTools\Commands\FormRequestsScaffoldCommand;
use Saritasa\LaravelTools\Database\DatabaseConnectionManager;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\Factories\FormRequestFactory;
use Saritasa\LaravelTools\Mappings\DbalToLaravelValidationTypeMapper;
use Saritasa\LaravelTools\Mappings\DbalToPhpTypeMapper;
use Saritasa\LaravelTools\Mappings\ILaravelValidationTypeMapper;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
use Saritasa\LaravelTools\Rules\RuleBuilder;

class LaravelToolsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
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

            $this->app->when(FormRequestFactory::class)
                ->needs(IPhpTypeMapper::class)
                ->give(DbalToPhpTypeMapper::class);

            $this->app->when(RuleBuilder::class)
                ->needs(ILaravelValidationTypeMapper::class)
                ->give(DbalToLaravelValidationTypeMapper::class);
        }
    }

    /**
     * Returns array with provided services.
     *
     * @return array
     */
    public function provides()
    {
        return [FormRequestFactory::class];
    }
}
