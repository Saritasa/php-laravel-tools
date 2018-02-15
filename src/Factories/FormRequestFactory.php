<?php

namespace Saritasa\LaravelTools\Factories;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Saritasa\Exceptions\ConfigurationException;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\FormRequestFactoryConfig;
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

    /**
     * Form request template name.
     */
    const TEMPLATE_NAME = 'FormRequestTemplate';

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
     * Target model's table columns.
     *
     * @var Column[]
     */
    private $columns;

    /**
     * Form Request class builder. Allows to create FormRequest class for model.
     * This form request class will contain model's attributes validation based on model's table structure.
     *
     * @param SchemaReader $schemaReader Database table information reader
     * @param TemplateWriter $templateWriter Templates files writer
     * @param RuleBuilder $ruleBuilder Column rule builder
     */
    public function __construct(SchemaReader $schemaReader, TemplateWriter $templateWriter, RuleBuilder $ruleBuilder)
    {
        $this->schemaReader = $schemaReader;
        $this->templateWriter = $templateWriter;
        $this->ruleBuilder = $ruleBuilder;
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
     * Returns model's table name for which need to build form request.
     *
     * @return string
     * @see configure method for details
     * @throws ConfigurationException When factory's configuration doesn't contain model class name
     * @throws UnexpectedValueException When passed model class is not a Model class instance
     */
    private function getTableName(): string
    {
        if (!$this->config->modelClassName) {
            throw new ConfigurationException('Form request model not configured');
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
     * Read model's columns information.
     *
     * @param string $getTableName Table name to retrieve columns information for
     *
     * @return void
     */
    private function readColumnsInformation(string $getTableName): void
    {
        $columns = $this->schemaReader->getColumnsDetails($getTableName);

        $this->columns = array_filter($columns, function (Column $column) {
            return !in_array($column->getName(), $this->config->excludedAttributes);
        });
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

        foreach ($this->columns as $column) {
            $columnName = $column->getName();
            $columnRule = $this->ruleBuilder->generateRules($column);
            $rules[] = "'{$columnName}' => '{$columnRule}'";
        }

        return $rules;
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
        // TODO improve hardcoded indent
        $indent = str_repeat(' ', 3 * 4);

        $formattedRules = implode(",\n{$indent}", $rules);

        return trim($formattedRules);
    }

    /**
     * Returns PHP-version of column type.
     *
     * @param Type $type Column type to retrieve php-type
     *
     * @return string
     */
    private function getColumnPHPType(Type $type): string
    {
        // TODO perform mapping
        return $type->getName();
    }

    /**
     * Format form request class PHPDoc properties like "property-read type $variable."
     *
     * @return string
     */
    private function getClassPropertiesDockBlock(): string
    {
        $docBlock = '';
        foreach ($this->columns as $column) {
            $property = $column->getName();
            $type = $this->getColumnPHPType($column->getType());
            $description = $column->getComment();
            $nullableType = $column->getNotnull() ? '' : '|null';

            $docBlock .= "* @property-read {$type}{$nullableType} \${$property} {$description}\n";
        }

        return trim($docBlock);
    }

    /**
     * Returns template's placeholders values.
     *
     * @return array
     * @throws Exception
     */
    private function getPlaceHoldersValues(): array
    {
        $rules = $this->buildRules();

        return [
            // TODO build USES array
            static::PLACEHOLDER_USES => '',
            static::PLACEHOLDER_READABLE_PROPERTIES => $this->getClassPropertiesDockBlock(),
            static::PLACEHOLDER_NAMESPACE => $this->config->namespace,
            static::PLACEHOLDER_FORM_REQUEST_CLASS_NAME => $this->config->className,
            static::PLACEHOLDER_FORM_REQUEST_PARENT => $this->config->parentClassName,
            static::PLACEHOLDER_RULES => $this->formatRules($rules),

        ];
    }

    /**
     * Build and write new form request file.
     *
     * @param FormRequestFactoryConfig $formRequestFactoryConfig Form request factory configuration
     *
     * @return void
     * @throws Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws ConfigurationException
     */
    public function build(FormRequestFactoryConfig $formRequestFactoryConfig): void
    {
        $this->configure($formRequestFactoryConfig);

        $this->readColumnsInformation($this->getTableName());

        $filledPlaceholders = $this->getPlaceHoldersValues();

        $this->templateWriter
            ->take($this->config->templateFilename)
            ->fill($filledPlaceholders)
            ->write($this->config->resultFilename);
    }
}
