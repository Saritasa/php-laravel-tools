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

    /**
     * API routes file template.
     */
    const API_ROUTES_TEMPLATE = 'ApiRoutesTemplate';

    /**
     * API controller class template.
     */
    const API_CONTROLLER_TEMPLATE = 'ClassTemplate';

    /**
     * General PHP class template.
     */
    const CLASS_TEMPLATE = 'ClassTemplate';
}
