<?php

namespace Saritasa\LaravelTools\DTO\Configs;

use Saritasa\Dto;

/**
 * Configuration for file by template generation.
 */
abstract class TemplateBasedFactoryConfig extends Dto
{
    const RESULT_FILENAME = 'resultFilename';
    const TEMPLATE_FILENAME = 'templateFilename';

    /**
     * Result file full name.
     *
     * @var
     */
    public $resultFilename;

    /**
     * Class template full path name.
     *
     * @var string
     */
    public $templateFilename;
}
