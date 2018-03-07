<?php

namespace Saritasa\LaravelTools\Rules;

/**
 * Validation rules dictionary with string representation of rules.
 */
class StringValidationRulesDictionary implements IValidationRulesDictionary
{
    /**
     * Attribute validation rules prefix. Will be added before first rule.
     *
     * @return string
     */
    public function rulesPrefix(): string
    {
        return '\'';
    }

    /**
     * Attribute validation rules suffix. Will be added after last rule.
     *
     * @return string
     */
    public function rulesSuffix(): string
    {
        return '\'';
    }

    /**
     * Attribute validation rules delimiter. Will be used as glue to combine rules.
     *
     * @return string
     */
    public function rulesDelimiter(): string
    {
        return '|';
    }

    /**
     * Rule 'required'.
     *
     * @return string
     */
    public function required(): string
    {
        return 'required';
    }

    /**
     * Rule 'nullable'.
     *
     * @return string
     */
    public function nullable(): string
    {
        return 'nullable';
    }

    /**
     * Rule 'array'.
     *
     * @return string
     */
    public function typeArray(): string
    {
        return 'array';
    }

    /**
     * Rule 'boolean'.
     *
     * @return string
     */
    public function typeBoolean(): string
    {
        return 'boolean';
    }

    /**
     * Rule 'date'.
     *
     * @return string
     */
    public function typeDate(): string
    {
        return 'date';
    }

    /**
     * Rule 'integer'.
     *
     * @return string
     */
    public function typeInteger(): string
    {
        return 'integer';
    }

    /**
     * Rule 'string'.
     *
     * @return string
     */
    public function typeString(): string
    {
        return 'string';
    }

    /**
     * Rule 'numeric'.
     *
     * @return string
     */
    public function typeNumeric(): string
    {
        return 'numeric';
    }

    /**
     * Rule 'max'.
     *
     * @param int $max Attribute value max length
     *
     * @return string
     */
    public function ruleMax(int $max): string
    {
        return "max:{$max}";
    }

    /**
     * Rule 'exists'.
     *
     * @param string $tableName In which table value should exists
     * @param string $column Which column should contain value
     *
     * @return string
     */
    public function ruleExists(string $tableName, string $column): string
    {
        return "exists:{$tableName},{$column}";
    }
}
