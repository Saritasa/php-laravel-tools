<?php

namespace Saritasa\LaravelTools\Factories;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use Exception;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\ClassPropertyObject;
use Saritasa\LaravelTools\DTO\FormRequestFactoryConfig;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
use Saritasa\LaravelTools\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\Rules\RuleBuilder;
use Saritasa\LaravelTools\Services\TemplateWriter;
use UnexpectedValueException;

/**
 * Form Request class builder. Allows to create FormRequest class for model.
 * This form request class will contain model's attributes validation based on model's table structure.
 */
class FormRequestFactory
{
    const PLACEHOLDER_NAMESPACE = 'namespace';
    const PLACEHOLDER_USES = 'uses';
    const PLACEHOLDER_FORM_REQUEST_CLASS_NAME = 'formRequestClassName';
    const CLASS_PHP_DOC = 'classPhpDoc';
    const PLACEHOLDER_FORM_REQUEST_PARENT = 'formRequestParent';
    const PLACEHOLDER_RULES = 'rules';

    private const INDENT_SIZE = 4;
    private const RULES_INDENTS = 3;

    /**
     * Form request builder configuration.
     *
     * @var FormRequestFactoryConfig
     */
    protected $config = null;

    /**
     * Database table information reader.
     *
     * @var SchemaReader
     */
    private $schemaReader;

    /**
     * Templates files writer.
     *
     * @var TemplateWriter
     */
    private $templateWriter;

    /**
     * Column rule builder.
     *
     * @var RuleBuilder
     */
    private $ruleBuilder;

    /**
     * Target model's table details.
     *
     * @var Table
     */
    private $table;

    /**
     * Target model's table columns.
     *
     * @var Column[]
     */
    private $columns;

    /**
     * Target model's foreign keys.
     *
     * @var ForeignKeyConstraint[]
     */
    private $foreignKeys;

    /**
     * Array with form request used classes.
     *
     * @var string[]
     */
    private $formRequestUsedClasses = [];

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
     * @param RuleBuilder $ruleBuilder Column rule builder
     * @param IPhpTypeMapper $phpTypeMapper Storage type to PHP scalar type mapper
     * @param PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder Allows to build PHPDoc class description
     */
    public function __construct(
        SchemaReader $schemaReader,
        TemplateWriter $templateWriter,
        RuleBuilder $ruleBuilder,
        IPhpTypeMapper $phpTypeMapper,
        PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder
    ) {
        $this->schemaReader = $schemaReader;
        $this->templateWriter = $templateWriter;
        $this->ruleBuilder = $ruleBuilder;
        $this->phpTypeMapper = $phpTypeMapper;
        $this->phpDocClassDescriptionBuilder = $phpDocClassDescriptionBuilder;
    }

    /**
     * Build and write new form request file.
     *
     * @param FormRequestFactoryConfig $formRequestFactoryConfig Form request configuration
     *
     * @return void
     * @throws Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function build(FormRequestFactoryConfig $formRequestFactoryConfig): void
    {
        $this->configure($formRequestFactoryConfig);

        $this->readTableInformation($this->getTableName());

        $filledPlaceholders = $this->getPlaceHoldersValues();

        $this->templateWriter
            ->take($this->config->templateFilename)
            ->fill($filledPlaceholders)
            ->write($this->config->resultFilename);
    }

    /**
     * Configure factory to build new form request.
     *
     * @param FormRequestFactoryConfig $config Form Request configuration
     *
     * @throws RuntimeException When factory's configuration doesn't contain model class name
     * @throws UnexpectedValueException When passed model class is not a Model class instance
     */
    private function configure(FormRequestFactoryConfig $config): void
    {
        $this->config = $config;

        if (!$this->config->modelClassName) {
            throw new RuntimeException('Form request model not configured');
        }

        if (!is_a($this->config->modelClassName, Model::class, true)) {
            throw new UnexpectedValueException(
                "Class [{$this->config->modelClassName}] is not a valid Model class name"
            );
        }
    }

    /**
     * Read model's table information.
     *
     * @param string $tableName Table name to retrieve information for
     *
     * @return void
     */
    private function readTableInformation(string $tableName): void
    {
        $this->table = $this->schemaReader->getTableDetails($tableName);

        $this->foreignKeys = [];
        foreach ($this->table->getForeignKeys() as $foreignKey) {
            $localColumn = $foreignKey->getLocalColumns()[0];
            $this->foreignKeys[$localColumn] = $foreignKey;
        };

        $this->columns = [];
        foreach ($this->table->getColumns() as $column) {
            if (!in_array($column->getName(), $this->config->excludedAttributes)) {
                $this->columns[$column->getName()] = $column;
            }
        }
    }

    /**
     * Returns model's table name for which need to build form request.
     *
     * @return string
     * @see configure method for details
     */
    private function getTableName(): string
    {
        /**
         * Model for which need to build form request.
         *
         * @var Model $model
         */
        $model = new $this->config->modelClassName();

        return $model->getTable();
    }

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    private function getPlaceHoldersValues(): array
    {
        $placeholders = [
            static::PLACEHOLDER_FORM_REQUEST_CLASS_NAME => $this->config->className,
            static::PLACEHOLDER_FORM_REQUEST_PARENT => '\\'.$this->config->parentClassName,
            static::CLASS_PHP_DOC => $this->getClassDocBlock(),
            static::PLACEHOLDER_RULES => $this->formatRules($this->buildRules()),
        ];

        array_walk($placeholders, function (&$placeholder) {
            $placeholder = $this->extractUsedClasses($placeholder);
        });

        $placeholders[static::PLACEHOLDER_NAMESPACE] = $this->config->namespace;
        $placeholders[static::PLACEHOLDER_USES] = $this->formatUsedClasses();

        return $placeholders;
    }

    /**
     * Format form request class PHPDoc properties like "@property-read {type} $variable".
     *
     * @return string
     * @throws RuntimeException
     */
    private function getClassDocBlock(): string
    {
        $classProperties = [];
        foreach ($this->columns as $column) {
            $classProperties[] = new ClassPropertyObject([
                ClassPropertyObject::NAME => $column->getName(),
                ClassPropertyObject::TYPE => $this->phpTypeMapper->getPhpType($column->getType()),
                ClassPropertyObject::NULLABLE => !$column->getNotnull(),
                ClassPropertyObject::DESCRIPTION => $column->getComment(),
                ClassPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
            ]);
        }

        $classDescription = "{$this->config->className} form request.";

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
        $indent = $this->getRulesIndent();

        $formattedRules = implode(",\n{$indent}", $rules);

        return trim($formattedRules);
    }

    /**
     * Returns rules indent.
     *
     * @return string
     */
    private function getRulesIndent(): string
    {
        return str_repeat(' ', static::INDENT_SIZE * static::RULES_INDENTS);
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
            $constantName = strtoupper($columnName);

            return "\\{$modelClassName}::{$constantName}";
        }

        return "'{$columnName}'";
    }

    /**
     * Extract and replace fully-qualified class names from placeholder.
     *
     * @param string $placeholder Placeholder to extract class names from
     *
     * @return string Optimized placeholder
     */
    public function extractUsedClasses($placeholder): string
    {
        $classNamespaceRegExp = '/(\\\\{1,2}[\\\\a-zA-Z0-9_]+)[:\n]{0,2}/';
        $matches = [];
        $optimizedPlaceholder = $placeholder;
        if (preg_match_all($classNamespaceRegExp, $placeholder, $matches)) {
            foreach ($matches[1] as $match) {
                $usedClassName = $match;
                $this->formRequestUsedClasses[] = trim($usedClassName, '\\');
                $namespaceParts = explode('\\', $usedClassName);
                $resultClassName = array_pop($namespaceParts);
                $optimizedPlaceholder = str_replace($usedClassName, $resultClassName, $optimizedPlaceholder);
            }
        }

        $this->formRequestUsedClasses = array_unique($this->formRequestUsedClasses);

        return $optimizedPlaceholder;
    }

    /**
     * Returns USE section of built form request class.
     *
     * @return string
     */
    private function formatUsedClasses(): string
    {
        $result = [];
        foreach ($this->formRequestUsedClasses as $usedClass) {
            $result[] = "use {$usedClass};";
        }

        sort($result);

        return implode("\n", $result);
    }
}
