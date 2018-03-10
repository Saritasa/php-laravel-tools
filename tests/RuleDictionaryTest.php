<?php

namespace Saritasa\LaravelTools\Tests;

use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\Rules\FluentValidationRulesDictionary;
use Saritasa\LaravelTools\Rules\StringValidationRulesDictionary;

/**
 * Test validation rules dictionaries.
 */
class RuleDictionaryTest extends TestCase
{
    /** @var StringValidationRulesDictionary */
    private $stringDictionary;

    /** @var FluentValidationRulesDictionary */
    private $fluentDictionary;

    protected function setUp()
    {
        parent::setUp();
        $this->stringDictionary = new StringValidationRulesDictionary();
        $this->fluentDictionary = new FluentValidationRulesDictionary();
    }

    /**
     * Test string rule set dictionary.
     *
     * @return void
     */
    public function testStringDictionary()
    {
        // Build mindless string rule just for test purposes
        $rules = [];
        $rules[] = $this->stringDictionary->required();
        $rules[] = $this->stringDictionary->nullable();
        $rules[] = $this->stringDictionary->ruleExists('table', 'column');
        $rules[] = $this->stringDictionary->ruleMax(10);
        $rules[] = $this->stringDictionary->typeArray();
        $rules[] = $this->stringDictionary->typeBoolean();
        $rules[] = $this->stringDictionary->typeDate();
        $rules[] = $this->stringDictionary->typeInteger();
        $rules[] = $this->stringDictionary->typeNumeric();
        $rules[] = $this->stringDictionary->typeString();
        $builtRules = implode($this->stringDictionary->rulesDelimiter(), $rules);
        $prefix = $this->stringDictionary->rulesPrefix();
        $suffix = $this->stringDictionary->rulesSuffix();

        $actual = "{$prefix}{$builtRules}{$suffix}";
        $expected = '\'required|nullable|exists:table,column|max:10|array|boolean|date|integer|numeric|string\'';

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test fluent rule set dictionary.
     *
     * @return void
     */
    public function testFluentDictionary()
    {
        // Build mindless fluent rule just for test purposes
        $rules = [];
        $rules[] = $this->fluentDictionary->required();
        $rules[] = $this->fluentDictionary->nullable();
        $rules[] = $this->fluentDictionary->ruleExists('table', 'column');
        $rules[] = $this->fluentDictionary->ruleMax(10);
        $rules[] = $this->fluentDictionary->typeArray();
        $rules[] = $this->fluentDictionary->typeBoolean();
        $rules[] = $this->fluentDictionary->typeDate();
        $rules[] = $this->fluentDictionary->typeInteger();
        $rules[] = $this->fluentDictionary->typeNumeric();
        $rules[] = $this->fluentDictionary->typeString();
        $builtRules = implode($this->fluentDictionary->rulesDelimiter(), $rules);
        $prefix = $this->fluentDictionary->rulesPrefix();
        $suffix = $this->fluentDictionary->rulesSuffix();

        $actual = "{$prefix}{$builtRules}{$suffix}";
        $expected = '\Saritasa\Laravel\Validation\Rule::required()->nullable()->exists(\'table\', \'column\')' .
            '->max(10)->array()->boolean()->date()->int()->numeric()->string()';

        $this->assertEquals($expected, $actual);
    }
}
