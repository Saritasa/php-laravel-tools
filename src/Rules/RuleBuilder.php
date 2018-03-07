<?php

namespace Saritasa\LaravelTools\Rules;

use Doctrine\DBAL\Schema\Column;
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
     * @var RuleDictionary
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
     * @param RuleDictionary $ruleDictionary Rule dictionary that allows to retrieve rule
     * @param ILaravelValidationTypeMapper $validationTypeMapper Storage-specific type to validation type mapper
     */
    public function __construct(RuleDictionary $ruleDictionary, ILaravelValidationTypeMapper $validationTypeMapper)
    {
        $this->ruleDictionary = $ruleDictionary;
        $this->validationTypeMapper = $validationTypeMapper;
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
        // Check is column required or can be nullable
        $this->checkRequired($column);
        // Apply column type validation
        $this->applyTypeRule($column);
        // Apply column max length rule
        $this->checkMaxLength($column);

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
    private function checkRequired(Column $column): void
    {
        $this->appendRule($column->getNotnull()
            ? $this->ruleDictionary->required()
            : $this->ruleDictionary->nullable());
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
    private function checkMaxLength(Column $column): void
    {
        if ($column->getType()->getName() === Type::STRING || $column->getType()->getName() === Type::TEXT) {
            $maxLength = $column->getLength();
            $this->appendRule($this->ruleDictionary->ruleMax($maxLength));
        }
    }
}
