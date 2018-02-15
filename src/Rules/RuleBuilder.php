<?php

namespace Saritasa\LaravelTools\Rules;

use Doctrine\DBAL\Schema\Column;

/**
 * Rule builder. Allows to build validation rules set for column.
 */
class RuleBuilder
{
    /**
     * Rule dictionary that allows to retrieve rule.
     *
     * @var RuleDictionary
     */
    private $ruleDictionary;

    /**
     * Prepared rules array.
     *
     * @var array
     */
    private $rules = [];

    // TODO map with laravel rules types
    private $typesMapping = [
        'string' => ['varchar', 'text', 'string', 'char', 'enum', 'tinytext', 'mediumtext', 'longtext'],
        'date' => ['datetime', 'year', 'date', 'time', 'timestamp'],
        'int' => ['bigint', 'int', 'integer', 'tinyint', 'smallint', 'mediumint'],
        'float' => ['float', 'decimal', 'numeric', 'dec', 'fixed', 'double', 'real', 'double precision'],
        'boolean' => ['longblob', 'blob', 'bit'],
    ];

    /**
     * Rule builder. Allows to build validation rules set for column.
     *
     * @param RuleDictionary $ruleDictionary Rule dictionary that allows to retrieve rule
     */
    public function __construct(RuleDictionary $ruleDictionary)
    {
        $this->ruleDictionary = $ruleDictionary;
    }


    /**
     * Generates rules for column.
     *
     * @param Column $column Column to generate rules for
     *
     * @return string
     */
    public function generateRules(Column $column): string
    {
        $this->rules = [];

        $this->checkRequired($column);

        $this->appendRule($column->getColumnDefinition() ?? $column->getType()->getName());

        return $this->ruleDictionary->rulesPrefix() . implode($this->ruleDictionary->rulesDelimiter(), $this->rules);
    }

    /**
     * Checks is column is required and applies necessary rules
     *
     * @param Column $column Column to check
     *
     * @return void
     */
    private function checkRequired(Column $column): void
    {
        $this->appendRule($column->getNotnull() ? $this->ruleDictionary->required() : $this->ruleDictionary->nullable());
    }

    /**
     * Append rule to column rules set.
     *
     * @param string $rule Rule to append;
     *
     * @return void
     */
    private function appendRule(string $rule)
    {
        $this->rules[] = $rule;
    }
}
