<?php

namespace Saritasa\LaravelTools\Rules;

/**
 * Validation rules dictionary.
 */
interface IValidationRulesDictionary
{
    /**
     * Attribute validation rules prefix. Will be added before first rule.
     *
     * @return string
     */
    public function rulesPrefix(): string;

    /**
     * Attribute validation rules suffix. Will be added after last rule.
     *
     * @return string
     */
    public function rulesSuffix(): string;

    /**
     * Attribute validation rules delimiter. Will be used as glue to combine rules.
     *
     * @return string
     */
    public function rulesDelimiter(): string;

    /**
     * Rule 'required'.
     *
     * @return string
     */
    public function required(): string;

    /**
     * Rule 'nullable'.
     *
     * @return string
     */
    public function nullable(): string;

    /**
     * Rule 'boolean'.
     *
     * @return string
     */
    public function typeBoolean(): string;

    /**
     * Rule 'date'.
     *
     * @return string
     */
    public function typeDate(): string;

    /**
     * Rule 'integer'.
     *
     * @return string
     */
    public function typeInteger(): string;

    /**
     * Rule 'string'.
     *
     * @return string
     */
    public function typeString(): string;

    /**
     * Rule 'numeric'.
     *
     * @return string
     */
    public function typeNumeric(): string;

    /**
     * Rule 'max'.
     *
     * @param int $max Attribute value max length
     *
     * @return string
     */
    public function ruleMax(int $max): string;

    /**
     * Rule 'exists'.
     *
     * @param string $tableName In which table value should exists
     * @param string $column Which column should contain value
     *
     * @return string
     */
    public function ruleExists(string $tableName, string $column): string;
}
