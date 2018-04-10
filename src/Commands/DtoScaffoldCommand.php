<?php

namespace Saritasa\LaravelTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
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
                            {model? : The name of the model. When not passed then DTO for all models will be generated}
                            {dto? : The name of new DTO to scaffold}
                            {--I|immutable : New DTO should be immutable. Config value used when not passed}
                            {--S|strict : New DTO should be strict typed. Config value used when not passed}
                            {--C|constants : Add constants with attributes names. Config value used when not passed}';

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
     * Configurations storage.
     *
     * @var Repository
     */
    private $configRepository;

    /**
     * Console command to generate new Dto based on model's attributes.
     *
     * @param DtoService $dtoService DTO scaffold service
     * @param Repository $configRepository Configurations storage
     */
    public function __construct(DtoService $dtoService, Repository $configRepository)
    {
        parent::__construct();

        $this->dtoService = $dtoService;
        $this->configRepository = $configRepository;
    }

    /**
     * Build DTO for given model class name.
     *
     * @param string $modelClassName Model class name with attributes to take
     * @param string $dtoClassName Reslt DTO class name
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function buildDTOForModel(string $modelClassName, string $dtoClassName): void
    {
        $dtoFactoryConfig = $this->getPreConfig();

        $resultFileName = $this->dtoService->generateDto($modelClassName, $dtoClassName, $dtoFactoryConfig);

        $this->info("Check out generated file [{$resultFileName}]");
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $modelClassName = $this->getModelClass();

        if ($modelClassName) {
            $dtoClassName = $this->getDtoClassName() ?? $this->suggestDtoClassName($modelClassName);
            $this->buildDTOForModel($modelClassName, $dtoClassName);

            return;
        }

        if (!$this->confirm('No model class provided. DTOs for all models will be generated')) {
            $this->warn('Please, specify model class name');

            return;
        }

        $modelsPath = $this->configRepository->get('laravel_tools.models.path');
        $modelsFiles = scandir($modelsPath);
        foreach ($modelsFiles as $modelFile) {
            $modelPath = $modelsPath . DIRECTORY_SEPARATOR . $modelFile;
            if (is_file($modelPath) && !is_dir($modelPath)) {
                $modelClass = basename($modelFile, '.php');
                $dtoClass = $this->suggestDtoClassName($modelClass);

                $this->buildDTOForModel($modelClass, $dtoClass);
            }
        }
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
     * Get model class name to which need to build DTO.
     *
     * @return string|null
     */
    protected function getModelClass(): ?string
    {
        return $this->argument('model');
    }

    /**
     * Get DTO class name.
     *
     * @return string|null
     */
    protected function getDtoClassName(): ?string
    {
        return $this->argument('dto');
    }

    /**
     * Suggest DTO class name by model class name.
     *
     * @param string $modelClassName Target model class name that will be used to generate DTO class name
     *
     * @return string
     */
    protected function suggestDtoClassName(string $modelClassName): string
    {
        return Str::studly($modelClassName . 'Data');
    }
}
