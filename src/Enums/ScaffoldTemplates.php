<?php

namespace Saritasa\LaravelTools\Enums;

use Saritasa\Enum;

/**
 * Available package's templates.
 */
class ScaffoldTemplates extends Enum
{
    /**
     * Form Request class template.
     */
    const FORM_REQUEST_TEMPLATE = 'FormRequestTemplate';

    /**
     * DTO class template.
     */
    const DTO_TEMPLATE = 'ModelDtoTemplate';
}
