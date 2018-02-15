<?php

namespace Saritasa\LaravelTools\Commands;

use Exception;
use Illuminate\Console\Command;
use Saritasa\LaravelTools\Services\FormRequestService;

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
     * @var FormRequestService
     */
    private $formRequestService;

    /**
     * Console command to generate new FormRequest based on model's attributes.
     *
     * @param FormRequestService $formRequestService Form request scaffold service
     */
    public function __construct(FormRequestService $formRequestService)
    {
        parent::__construct();

        $this->formRequestService = $formRequestService;
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
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle()
    {
        $formRequestClassName = $this->getFormRequestClassName();
        $modelClassName = $this->getModelClass();

        $this->formRequestService->generateFormRequest($modelClassName, $formRequestClassName);
    }
}
