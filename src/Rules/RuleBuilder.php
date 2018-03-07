<?php

namespace Saritasa\LaravelTools\Rules;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Types\Type;
use Saritasa\LaravelTools\Enums\LaravelValidationTypes;
use Saritasa\LaravelTools\Mappings\ILaravelValidationTypeMapper;

/**
 * Rule builder. Allows to build validation rules set for column.
 */
class RuleBuilder
{
    /**
     * Rule dictionary that allows to retrieve rule.
     *
     * @var IValidationRulesDictionary
     */
    private $ruleDictionary;

    /**
     * Prepared rules array.
     *
     * @var array
     */
    private $rules = [];

    /**
     * Storage-specific type to validation type mapper.
     *
     * @var ILaravelValidationTypeMapper
     */
    private $validationTypeMapper;

    /**
     * Rule builder. Allows to build validation rules set for column.
     *
     * @param IValidationRulesDictionary $ruleDictionary Rule dictionary that allows to retrieve rule
     * @param ILaravelValidationTypeMapper $validationTypeMapper Storage-specific type to validation type mapper
     */
    public function __construct(
        IValidationRulesDictionary $ruleDictionary,
        ILaravelValidationTypeMapper $validationTypeMapper
    ) {
        $this->ruleDictionary = $ruleDictionary;
        $this->validationTypeMapper = $validationTypeMapper;
    }

    /**
     * Generates rules for column.
     *
     * @param Column $column Column to generate rules for
     * @param ForeignKeyConstraint|null $foreignKeyConstraint Column foreign key constraints
     *
     * @return string
     */
    public function generateRules(Column $column, ?ForeignKeyConstraint $foreignKeyConstraint): string
    {
        $this->rules = [];

        // Check is column required
        $this->applyRequiredRule($column);

        // Check is column can be null
        $this->applyNullableRule($column);

        // Check existence in related table
        if ($foreignKeyConstraint) {
            $this->applyExistsRule($foreignKeyConstraint);
        }

        // Apply column type validation
        $this->applyTypeRule($column);

        // Apply column max length rule
        $this->applyMaxLengthRule($column);

        //Build rule string
        return $this->buildRuleString();
    }

    /**
     * Checks is column is required and applies necessary rules.
     *
     * @param Column $column Column to check
     *
     * @return void
     */
    private function applyRequiredRule(Column $column): void
    {
        if ($column->getNotnull()) {
            $this->appendRule($this->ruleDictionary->required());
        }
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

    /**
     * Checks is column is nullable and applies necessary rules.
     *
     * @param Column $column Column to check
     *
     * @return void
     */
    private function applyNullableRule(Column $column): void
    {
        if (!$column->getNotnull()) {
            $this->appendRule($this->ruleDictionary->nullable());
        }
    }

    /**
     * Check existence in related table.
     *
     * @param ForeignKeyConstraint $foreignKeyConstraint Column foreign key constraints
     *
     * @return void
     */
    private function applyExistsRule(ForeignKeyConstraint $foreignKeyConstraint): void
    {
        $foreignTableName = $foreignKeyConstraint->getForeignTableName();
        $foreignKey = $foreignKeyConstraint->getForeignColumns()[0];
        $this->appendRule($this->ruleDictionary->ruleExists($foreignTableName, $foreignKey));
    }

    /**
     * Apply validation type rule.
     *
     * @param Column $column Column to retrieve type from
     *
     * @return void
     */
    private function applyTypeRule(Column $column): void
    {
        $laravelValidationType = $this->validationTypeMapper->getValidationType($column->getType()->getName());

        switch ($laravelValidationType) {
            case LaravelValidationTypes::BOOLEAN:
                $typeRule = $this->ruleDictionary->typeBoolean();
                break;
            case LaravelValidationTypes::STRING:
                $typeRule = $this->ruleDictionary->typeString();
                break;
            case LaravelValidationTypes::ARRAY:
                $typeRule = $this->ruleDictionary->typeArray();
                break;
            case LaravelValidationTypes::DATE:
                $typeRule = $this->ruleDictionary->typeDate();
                break;
            case LaravelValidationTypes::INTEGER:
                $typeRule = $this->ruleDictionary->typeInteger();
                break;
            case LaravelValidationTypes::NUMERIC:
                $typeRule = $this->ruleDictionary->typeNumeric();
                break;
            default:
                $typeRule = null;
        }

        if ($typeRule) {
            $this->appendRule($typeRule);
        }
    }

    /**
     * Append max length rule for string column types.
     *
     * @param Column $column Column to check
     *
     * @return void
     */
    private function applyMaxLengthRule(Column $column): void
    {
        $maxLength = $column->getLength();
        $supportedMaxLengthTypes = [Type::STRING, Type::TEXT];
        if ($maxLength && in_array($column->getType()->getName(), $supportedMaxLengthTypes)) {
            $this->appendRule($this->ruleDictionary->ruleMax($maxLength));
        }
    }

    /**
     * Builds rule string.
     *
     * @return string
     */
    private function buildRuleString(): string
    {
        $prefix = $this->ruleDictionary->rulesPrefix();
        $suffix = $this->ruleDictionary->rulesSuffix();
        $delimiter = $this->ruleDictionary->rulesDelimiter();

        return $prefix . implode($delimiter, $this->rules) . $suffix;
    }
}
