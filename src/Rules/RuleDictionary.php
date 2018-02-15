<?php

namespace Saritasa\LaravelTools\Rules;

/**
 * TODO extract interface ad implement second rule dictionary with fluent validation
 */
class RuleDictionary
{
    public function rulesPrefix(): string
    {
        return '';
    }

    public function rulesDelimiter(): string
    {
        return '|';
    }

    public function required(): string
    {
        return 'required';
    }

    public function nullable(): string
    {
        return 'nullable';
    }
}
