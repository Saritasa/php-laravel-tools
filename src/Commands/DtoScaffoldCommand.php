<?php

namespace Saritasa\LaravelTools\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Saritasa\LaravelTools\DTO\DtoFactoryConfig;
use Saritasa\LaravelTools\Services\DtoService;

/**
 * Console command to generate new DTO based on model's attributes.
 */
class DtoScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto
                            {model : The name of the model}
                            {dto? : The name of new DTO to scaffold}
                            {--I|immutable : New DTO should be immutable. When not passed config value will be taken}
                            {--S|strict : New DTO should be with strict typed getters and setters. When not passed config value will be taken}
                            {--C|constants : New DTO should contain constants with attributes names. When not passed config value will be taken}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create DTO for model attributes';

    /**
     * DTO scaffold service.
     *
     * @var DtoService
     */
    private $dtoService;

    /**
     * Console command to generate new Dto based on model's attributes.
     *
     * @param DtoService $dtoService DTO scaffold service
     */
    public function __construct(DtoService $dtoService)
    {
        parent::__construct();

        $this->dtoService = $dtoService;
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        $modelClassName = $this->getModelClass();
        $dtoClassName = $this->getDtoClassName($modelClassName);

        $dtoFactoryConfig = $this->getPreConfig();

        $resultFileName = $this->dtoService->generateDto($modelClassName, $dtoClassName, $dtoFactoryConfig);

        $this->info("Check out generated file [{$resultFileName}]");
    }

    /**
     * Get user preferences for new DTO.
     *
     * @return DtoFactoryConfig
     */
    private function getPreConfig(): DtoFactoryConfig
    {
        // Check for constants preference
        $withConstants = $this->option('constants') ?? null;

        // Check for immutable DTO preferences
        $immutable = boolval($this->option('immutable')) ?? null;

        // Check for strict type preferences
        $strict = boolval($this->option('strict')) ?? null;

        return new DtoFactoryConfig([
            DtoFactoryConfig::IMMUTABLE => $immutable,
            DtoFactoryConfig::STRICT_TYPES => $strict,
            DtoFactoryConfig::WITH_CONSTANTS => $withConstants,
        ]);
    }

    /**
     * Get DTO class name.
     *
     * @param string $modelClassName Target model class name that will be used to generate DTO class name
     *
     * @return string
     */
    protected function getDtoClassName(string $modelClassName): ?string
    {
        $dtoClassName = $this->argument('dto');

        if (!$dtoClassName) {
            $suggestedClassName = Str::studly($modelClassName . 'Data');
            $dtoClassName = $this->ask('Please, enter new DTO class name', $suggestedClassName);
        }

        return $dtoClassName;
    }

    /**
     * Get model class name to which need to build DTO.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return $this->argument('model');
    }
}
