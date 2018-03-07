<?php

namespace Saritasa\LaravelTools\Factories;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Exception;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\FormRequestFactoryConfig;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
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
    const PLACEHOLDER_READABLE_PROPERTIES = 'readableProperties';
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
     * Form Request class builder. Allows to create FormRequest class for model.
     * This form request class will contain model's attributes validation based on model's table structure.
     *
     * @param SchemaReader $schemaReader Database table information reader
     * @param TemplateWriter $templateWriter Templates files writer
     * @param RuleBuilder $ruleBuilder Column rule builder
     * @param IPhpTypeMapper $phpTypeMapper Storage type to PHP scalar type mapper
     */
    public function __construct(
        SchemaReader $schemaReader,
        TemplateWriter $templateWriter,
        RuleBuilder $ruleBuilder,
        IPhpTypeMapper $phpTypeMapper
    ) {
        $this->schemaReader = $schemaReader;
        $this->templateWriter = $templateWriter;
        $this->ruleBuilder = $ruleBuilder;
        $this->phpTypeMapper = $phpTypeMapper;
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
     */
    private function configure(FormRequestFactoryConfig $config): void
    {
        $this->config = $config;
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
     * @throws RuntimeException When factory's configuration doesn't contain model class name
     * @throws UnexpectedValueException When passed model class is not a Model class instance
     */
    private function getTableName(): string
    {
        if (!$this->config->modelClassName) {
            throw new RuntimeException('Form request model not configured');
        }

        if (!is_a($this->config->modelClassName, Model::class, true)) {
            throw new UnexpectedValueException(
                "Class [{$this->config->modelClassName}] is not a valid Model class name"
            );
        }

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
        return [
            static::PLACEHOLDER_NAMESPACE => $this->config->namespace,
            static::PLACEHOLDER_FORM_REQUEST_CLASS_NAME => $this->config->className,
            static::PLACEHOLDER_FORM_REQUEST_PARENT => $this->config->parentClassName,
            static::PLACEHOLDER_READABLE_PROPERTIES => $this->getClassPropertiesDockBlock(),
            static::PLACEHOLDER_RULES => $this->formatRules($this->buildRules()),
            static::PLACEHOLDER_USES => $this->formatUsedClasses(),
        ];
    }

    /**
     * Format form request class PHPDoc properties like "@property-read {type} $variable".
     *
     * @return string
     * @throws RuntimeException
     */
    private function getClassPropertiesDockBlock(): string
    {
        $docBlockProperties = [];
        foreach ($this->columns as $column) {
            $propertyName = $column->getName();
            $type = $this->getColumnPHPType($column->getType());
            $description = $column->getComment();
            $nullableType = $column->getNotnull() ? '' : '|null';

            $docBlockProperties[] = trim("* @property-read {$type}{$nullableType} \${$propertyName} {$description}");
        }

        return implode("\n", $docBlockProperties);
    }

    /**
     * Returns PHP-version of column type.
     *
     * @param Type $type Column type to retrieve php-type
     *
     * @return string
     * @throws RuntimeException
     */
    private function getColumnPHPType(Type $type): string
    {
        return $this->phpTypeMapper->getPhpType($type->getName());
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
            $this->formRequestUsedClasses[] = $this->config->modelClassName;
            $modelClassName = array_last(explode('\\', $this->config->modelClassName));
            $constantName = strtoupper($columnName);

            $this->formRequestUsedClasses = array_unique($this->formRequestUsedClasses);

            return "{$modelClassName}::{$constantName}";
        }

        return "'{$columnName}'";
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
