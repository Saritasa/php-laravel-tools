<?php

namespace Saritasa\LaravelTools\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Saritasa\LaravelTools\Services\GenerationServices\FormRequestGenerationService;

/**
 * Console command to generate new FormRequest based on model's attributes.
 */
class FormRequestsScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:form_request
                            {model : The name of the model}
                            {request? : The name of new form request to scaffold}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create FormRequest for model';

    /**
     * Form request scaffold service.
     *
     * @var FormRequestGenerationService
     */
    private $formRequestService;

    /**
     * Console command to generate new FormRequest based on model's attributes.
     *
     * @param FormRequestGenerationService $formRequestService Form request scaffold service
     */
    public function __construct(FormRequestGenerationService $formRequestService)
    {
        parent::__construct();

        $this->formRequestService = $formRequestService;
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        $modelClassName = $this->getModelClass();
        $formRequestClassName = $this->getFormRequestClassName();

        if (!$formRequestClassName) {
            $suggestedClassName = $this->generateFormRequestClassName($modelClassName);
            $formRequestClassName = $this->ask('Please, enter new form request class name', $suggestedClassName);
        }

        $resultFileName = $this->formRequestService->generateFormRequest($modelClassName, $formRequestClassName);

        $this->info("Check out generated file [{$resultFileName}]");
    }

    /**
     * Get form request class name.
     *
     * @return string
     */
    protected function getFormRequestClassName(): ?string
    {
        return $this->argument('request');
    }

    /**
     * Generate form request class name from model class name.
     *
     * @param string $modelClassName Model class name to generate form request for
     *
     * @return string
     */
    protected function generateFormRequestClassName(string $modelClassName)
    {
        $formRequestClassName = $modelClassName . 'Request';

        return Str::studly($formRequestClassName);
    }

    /**
     * Get model class name to which need to build form request.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return $this->argument('model');
    }
}
