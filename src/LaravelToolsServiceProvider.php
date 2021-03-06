<?php

namespace Saritasa\LaravelTools;

use Doctrine\DBAL\Connection;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\IApiRouteGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\IApiRoutesBlockGenerator;
use Saritasa\LaravelTools\CodeGenerators\ApiRoutesDefinition\IApiRoutesGroupGenerator;
use Saritasa\LaravelTools\Commands\ApiControllersScaffoldCommand;
use Saritasa\LaravelTools\Commands\ApiRoutesScaffoldCommand;
use Saritasa\LaravelTools\Commands\DtoScaffoldCommand;
use Saritasa\LaravelTools\Commands\FormRequestsScaffoldCommand;
use Saritasa\LaravelTools\Database\DatabaseConnectionManager;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\Factories\DtoFactory;
use Saritasa\LaravelTools\Factories\FormRequestFactory;
use Saritasa\LaravelTools\Mappings\DbalToLaravelValidationTypeMapper;
use Saritasa\LaravelTools\Mappings\DbalToPhpTypeMapper;
use Saritasa\LaravelTools\Mappings\ILaravelValidationTypeMapper;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
use Saritasa\LaravelTools\Mappings\SwaggerToPhpTypeMapper;
use Saritasa\LaravelTools\Rules\IValidationRulesDictionary;
use Saritasa\LaravelTools\Swagger\SwaggerReader;

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

            $this->registerCommands();

            $this->registerBindings();
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

    /**
     * Register artisan commands, provided by this package.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        $this->commands([
            FormRequestsScaffoldCommand::class,
            DtoScaffoldCommand::class,
            ApiRoutesScaffoldCommand::class,
            ApiControllersScaffoldCommand::class,
        ]);
    }

    /**
     * Register package dependencies.
     *
     * @return void
     */
    private function registerBindings(): void
    {
        $this->app->when(SchemaReader::class)
            ->needs(Connection::class)
            ->give(function (Container $app) {
                return $app->make(DatabaseConnectionManager::class)->getConnection();
            });

        $this->app->when(DtoFactory::class)->needs(IPhpTypeMapper::class)->give(DbalToPhpTypeMapper::class);
        $this->app->when(FormRequestFactory::class)->needs(IPhpTypeMapper::class)->give(DbalToPhpTypeMapper::class);
        $this->app->when(SwaggerReader::class)->needs(IPhpTypeMapper::class)->give(SwaggerToPhpTypeMapper::class);

        $this->app->bind(ILaravelValidationTypeMapper::class, DbalToLaravelValidationTypeMapper::class);
        $this->app->bind(IValidationRulesDictionary::class, config('laravel_tools.rules.dictionary'));

        // API-routes and controllers related bindings
        $this->app->bind(IApiRouteGenerator::class, config('laravel_tools.api_routes.route_generator'));
        $this->app->bind(IApiRoutesBlockGenerator::class, config('laravel_tools.api_routes.block_generator'));
        $this->app->bind(IApiRoutesGroupGenerator::class, config('laravel_tools.api_routes.group_generator'));
    }
}
