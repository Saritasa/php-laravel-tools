<?php

namespace Saritasa\LaravelTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Saritasa\LaravelTools\DTO\Configs\ApiRoutesFactoryConfig;
use Saritasa\LaravelTools\Services\ApiRoutesDefinitionGenerationService;

/**
 * Console command to generate api routes based on swagger 2.0 specification.
 */
class ApiRoutesScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create api routes based on swagger 2.0 specification';

    /**
     * Generates api routes definition based on swagger specification.
     *
     * @var ApiRoutesDefinitionGenerationService
     */
    private $apiRoutesService;

    /**
     * Console command to generate api routes based on swagger 2.0 specification.
     *
     * @param ApiRoutesDefinitionGenerationService $apiRoutesService Generates api routes definition based on swagger specification
     */
    public function __construct(ApiRoutesDefinitionGenerationService $apiRoutesService)
    {
        parent::__construct();

        $this->apiRoutesService = $apiRoutesService;
    }


    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $resultFileName = $this->apiRoutesService->generateApiRoutes(new ApiRoutesFactoryConfig([]));
        $this->info("Check out generated file [{$resultFileName}]");
    }
}
