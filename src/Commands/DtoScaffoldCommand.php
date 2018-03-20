<?php

namespace Saritasa\LaravelTools\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
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
                            {dto? : The name of new DTO to scaffold}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create DTO for model';

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
        $dtoClassName = $this->getDtoClassName();

        if (!$dtoClassName) {
            $suggestedClassName = $this->generateDtoClassName($modelClassName);
            $dtoClassName = $this->ask('Please, enter new DTO class name', $suggestedClassName);
        }

        $resultFileName = $this->dtoService->generateDto($modelClassName, $dtoClassName);

        $this->info("Check out generated file [{$resultFileName}]");
    }

    /**
     * Get DTO class name.
     *
     * @return string
     */
    protected function getDtoClassName(): ?string
    {
        return $this->argument('dto');
    }

    /**
     * Generate DTO class name from model class name.
     *
     * @param string $modelClassName Model class name to generate DTO for
     *
     * @return string
     */
    protected function generateDtoClassName(string $modelClassName)
    {
        $dtoClassName = $modelClassName . 'Data';

        return Str::studly($dtoClassName);
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
