<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Support\Str;
use Saritasa\Exceptions\NotImplementedException;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\NamespaceExtractor;
use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\Configs\FormRequestFactoryConfig;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassPhpDocPropertyObject;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
use Saritasa\LaravelTools\Rules\RuleBuilder;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Form Request class builder. Allows to create FormRequest class for model.
 * This form request class will contain model's attributes validation based on model's table structure.
 */
class FormRequestFactory extends ModelBasedClassFactory
{
    protected const PLACEHOLDER_NAMESPACE = 'namespace';
    protected const PLACEHOLDER_IMPORTS = 'imports';
    protected const PLACEHOLDER_FORM_REQUEST_CLASS_NAME = 'formRequestClassName';
    protected const PLACEHOLDER_CLASS_PHP_DOC = 'classPhpDoc';
    protected const PLACEHOLDER_FORM_REQUEST_PARENT = 'formRequestParent';
    protected const PLACEHOLDER_RULES = 'rules';
    private const RULES_INDENTS = 3;

    /**
     * Form request builder configuration.
     *
     * @var FormRequestFactoryConfig
     */
    protected $config = null;

    /**
     * Column rule builder.
     *
     * @var RuleBuilder
     */
    private $ruleBuilder;

    /**
     * Storage type to PHP scalar type mapper.
     *
     * @var IPhpTypeMapper
     */
    private $phpTypeMapper;

    /**
     * Allows to build PHPDoc class description.
     *
     * @var PhpDocClassDescriptionBuilder
     */
    private $phpDocClassDescriptionBuilder;

    /**
     * Form Request class builder. Allows to create FormRequest class for model.
     * This form request class will contain model's attributes validation based on model's table structure.
     *
     * @param SchemaReader $schemaReader Database table information reader
     * @param TemplateWriter $templateWriter Templates files writer
     * @param NamespaceExtractor $namespaceExtractor Namespace extractor. Allows to retrieve list of used namespaces
     *     from code and remove FQN from it
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings
     * @param RuleBuilder $ruleBuilder Column rule builder
     * @param IPhpTypeMapper $phpTypeMapper Storage type to PHP scalar type mapper
     * @param PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder Allows to build PHPDoc class description
     */
    public function __construct(
        SchemaReader $schemaReader,
        TemplateWriter $templateWriter,
        NamespaceExtractor $namespaceExtractor,
        CodeFormatter $codeFormatter,
        RuleBuilder $ruleBuilder,
        IPhpTypeMapper $phpTypeMapper,
        PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder
    ) {
        parent::__construct($templateWriter, $schemaReader, $namespaceExtractor, $codeFormatter);
        $this->ruleBuilder = $ruleBuilder;
        $this->phpTypeMapper = $phpTypeMapper;
        $this->phpDocClassDescriptionBuilder = $phpDocClassDescriptionBuilder;
    }

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    protected function getPlaceHoldersValues(): array
    {
        $placeholders = [
            static::PLACEHOLDER_FORM_REQUEST_CLASS_NAME => $this->config->className,
            static::PLACEHOLDER_FORM_REQUEST_PARENT => '\\' . $this->config->parentClassName,
            static::PLACEHOLDER_CLASS_PHP_DOC => $this->getClassDocBlock(),
            static::PLACEHOLDER_RULES => $this->formatRules($this->buildRules()),
        ];

        foreach ($placeholders as $placeholder => $value) {
            $placeholders[$placeholder] = $this->extractUsedClasses($value);
        }

        $placeholders[static::PLACEHOLDER_NAMESPACE] = $this->config->namespace;
        $placeholders[static::PLACEHOLDER_IMPORTS] = $this->formatUsedClasses();

        return $placeholders;
    }

    /**
     * Format form request class PHPDoc properties like "@property-read {type} $variable".
     *
     * @return string
     * @throws NotImplementedException
     */
    private function getClassDocBlock(): string
    {
        $classProperties = [];
        foreach ($this->columns as $column) {
            $classProperties[] = new ClassPhpDocPropertyObject([
                ClassPhpDocPropertyObject::NAME => $column->getName(),
                ClassPhpDocPropertyObject::TYPE => $this->phpTypeMapper->getPhpType($column->getType()),
                ClassPhpDocPropertyObject::NULLABLE => !$column->getNotnull(),
                ClassPhpDocPropertyObject::DESCRIPTION => $column->getComment(),
                ClassPhpDocPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
            ]);
        }

        $classDescription = "{$this->config->className} form request";

        return $this->phpDocClassDescriptionBuilder->render($classDescription, $classProperties);
    }

    /**
     * Format validation rules array as part of PHP class.
     *
     * @param array $rules Rules to format
     *
     * @return string
     */
    private function formatRules(array $rules): string
    {
        $formattedRules = implode(",\n", $rules);

        $formattedRules = $formattedRules ? $formattedRules . ',' : $formattedRules;

        return trim($this->codeFormatter->indentBlock($formattedRules, false, static::RULES_INDENTS));
    }

    /**
     * Builds rules for target model.
     *
     * @return array
     * @throws Exception
     */
    private function buildRules(): array
    {
        $rules = [];

        foreach ($this->columns as $columnName => $columnDetails) {
            $foreignKeyConstraints = $this->foreignKeys[$columnName] ?? null;
            $columnRule = $this->ruleBuilder->generateRules($columnDetails, $foreignKeyConstraints);
            $formattedAttributeName = $this->formatAttributeName($columnName);
            $rules[] = "{$formattedAttributeName} => {$columnRule}";
        }

        return $rules;
    }

    /**
     * Returns formatted column name for validation rules attribute.
     *
     * @param string $columnName Column name to format attribute name
     *
     * @return string
     */
    private function formatAttributeName(string $columnName): string
    {
        if ($this->config->suggestAttributeNamesConstants) {
            $modelClassName = $this->config->modelClassName;
            $constantName = Str::upper(Str::snake($columnName));

            return "\\{$modelClassName}::{$constantName}";
        }

        return "'{$columnName}'";
    }
}
