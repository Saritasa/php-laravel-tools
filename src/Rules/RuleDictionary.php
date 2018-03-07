<?php

namespace Saritasa\LaravelTools\Rules;

/**
 * TODO extract interface and implement second rule dictionary with fluent validation
 */
class RuleDictionary
{
    public function rulesPrefix(): string
    {
        return '\'';
    }

    public function rulesSuffix(): string
    {
        return '\'';
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

    public function typeArray(): string
    {
        return 'array';
    }

    public function typeBoolean(): string
    {
        return 'boolean';
    }

    public function typeDate(): string
    {
        return 'date';
    }

    public function typeInteger(): string
    {
        return 'integer';
    }

    public function typeString(): string
    {
        return 'string';
    }

    public function typeNumeric(): string
    {
        return 'numeric';
    }

    public function ruleMax(int $max): string
    {
        return "max:{$max}";
    }
}
