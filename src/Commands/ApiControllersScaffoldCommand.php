<?php

namespace Saritasa\LaravelTools\Commands;

use Illuminate\Console\Command;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\Services\ApiControllerGenerationService;

/**
 * Console command to generate api controllers based on swagger 2.0 specification.
 */
class ApiControllersScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:api_controllers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create api controllers based on swagger 2.0 specification';

    /**
     * Service that can scaffold controllers and methods that covers api specification.
     *
     * @var ApiControllerGenerationService
     */
    private $apiControllerGenerationService;

    /**
     * Console command to generate api routes based on swagger 2.0 specification.
     *
     * @param ApiControllerGenerationService $apiControllerGenerationService Service that can scaffold controllers and
     *     methods that covers api specification
     */
    public function __construct(ApiControllerGenerationService $apiControllerGenerationService)
    {
        parent::__construct();

        $this->apiControllerGenerationService = $apiControllerGenerationService;
    }


    /**
     * Execute the console command.
     *
     * @throws ConfigurationException
     */
    public function handle(): void
    {
        $specificationFilename = $this->getSpecificationFileName();
        $this->info("'{$specificationFilename}' swagger file used.");

        $controllerNames = $this->apiControllerGenerationService->generateControllers($specificationFilename);

        $this->info("Check out generated files: \n\n\t" . implode("\n\t", $controllerNames));
    }

    /**
     * Returns full path to api specification.
     *
     * @return string
     */
    private function getSpecificationFileName(): string
    {
        return config('laravel_tools.swagger.path');
    }
}
