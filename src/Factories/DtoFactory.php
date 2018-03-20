<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\ClassFactoryConfig;
use Saritasa\LaravelTools\DTO\ClassPropertyObject;
use Saritasa\LaravelTools\DTO\DtoFactoryConfig;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
use Saritasa\LaravelTools\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\PhpDoc\PhpDocVariableDescriptionBuilder;
use Saritasa\LaravelTools\Services\TemplateWriter;
use UnexpectedValueException;

/**
 * DTO class builder. Allows to create Dto class for model.
 * This DTO class will contain model's attributes validation based on model's table structure.
 */
class DtoFactory extends ModelBasedClassFactory
{
    const PLACEHOLDER_NAMESPACE = 'namespace';
    const PLACEHOLDER_IMPORTS = 'imports';
    const PLACEHOLDER_DTO_CLASS_NAME = 'dtoClassName';
    const PLACEHOLDER_CLASS_PHP_DOC = 'classPhpDoc';
    const PLACEHOLDER_DTO_CONSTANTS = 'constants';
    const PLACEHOLDER_DTO_PROPERTIES = 'properties';
    const PLACEHOLDER_DTO_PARENT = 'dtoParent';

    /**
     * DTO builder configuration.
     *
     * @var DtoFactoryConfig
     */
    protected $config = null;

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
     * Allows to build PHPDoc for properties.
     *
     * @var PhpDocVariableDescriptionBuilder
     */
    private $variableDescriptionBuilder;

    /**
     * DTO class builder. Allows to create Dto class for model.
     * This DTO class will contain model's attributes validation based on model's table structure.
     *
     * @param SchemaReader $schemaReader Database table information reader
     * @param TemplateWriter $templateWriter Templates files writer
     * @param IPhpTypeMapper $phpTypeMapper Storage type to PHP scalar type mapper
     * @param PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder Allows to build PHPDoc class description
     * @param PhpDocVariableDescriptionBuilder $variableDescriptionBuilder Allows to build PHPDoc for properties
     */
    public function __construct(
        SchemaReader $schemaReader,
        TemplateWriter $templateWriter,
        IPhpTypeMapper $phpTypeMapper,
        PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder,
        PhpDocVariableDescriptionBuilder $variableDescriptionBuilder
    ) {
        parent::__construct($templateWriter, $schemaReader);
        $this->phpTypeMapper = $phpTypeMapper;
        $this->phpDocClassDescriptionBuilder = $phpDocClassDescriptionBuilder;
        $this->variableDescriptionBuilder = $variableDescriptionBuilder;
    }

    /**
     * Configure factory to build new DTO.
     *
     * @param DtoFactoryConfig|ClassFactoryConfig $config DTO configuration
     *
     * @throws RuntimeException When factory's configuration doesn't contain model class name
     * @throws UnexpectedValueException When passed model class is not a Model class instance
     *
     * @return DtoFactory
     */
    public function configure($config)
    {
        $this->config = $config;

        if (!$this->config->modelClassName) {
            throw new RuntimeException('DTO not configured');
        }

        if (!is_a($this->config->modelClassName, Model::class, true)) {
            throw new UnexpectedValueException(
                "Class [{$this->config->modelClassName}] is not a valid Model class name"
            );
        }

        return $this;
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
            static::PLACEHOLDER_DTO_CLASS_NAME => $this->config->className,
            static::PLACEHOLDER_DTO_PARENT => '\\' . $this->config->parentClassName,
            static::PLACEHOLDER_CLASS_PHP_DOC => $this->getClassDocBlock(),
            static::PLACEHOLDER_DTO_CONSTANTS => $this->getConstantsBlock(),
            static::PLACEHOLDER_DTO_PROPERTIES => $this->getPropertiesBlock(),
        ];

        foreach ($placeholders as $placeholder => $value) {
            $placeholders[$placeholder] = $this->extractUsedClasses($value);
        }

        $placeholders[static::PLACEHOLDER_NAMESPACE] = $this->config->namespace;
        $placeholders[static::PLACEHOLDER_IMPORTS] = $this->formatUsedClasses();

        return $placeholders;
    }

    /**
     * Format DTO class PHPDoc properties like "@property-read {type} $variable".
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

        $classDescription = "{$this->config->className} DTO";

        return $this->phpDocClassDescriptionBuilder->render($classDescription, $classProperties);
    }

    /**
     * Format DTO constants that are available attributes names.
     *
     * @return string
     * @throws RuntimeException
     */
    private function getConstantsBlock(): string
    {
        $classConstants = [];
        foreach ($this->columns as $column) {
            $classConstants[] = $this->getIndent() . $this->formatConstantDeclaration($column->getName());
        }

        return implode("\n", $classConstants);
    }

    /**
     * Format DTO constants that are available attributes names.
     *
     * @return string
     * @throws RuntimeException
     */
    private function getPropertiesBlock(): string
    {
        $classProperties = [];
        foreach ($this->columns as $column) {
            $classProperty = new ClassPropertyObject([
                ClassPropertyObject::NAME => $column->getName(),
                ClassPropertyObject::TYPE => $this->phpTypeMapper->getPhpType($column->getType()),
                ClassPropertyObject::NULLABLE => !$column->getNotnull(),
                ClassPropertyObject::DESCRIPTION => $column->getComment(),
                ClassPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
            ]);

            $classProperties[] = $this->variableDescriptionBuilder->render($classProperty, $this->getIndent());
            $classProperties[] = $this->getIndent() . "{$this->config->propertiesVisibility} \${$classProperty->name};";
            $classProperties[] = '';
        }

        return rtrim(implode("\n", $classProperties));
    }

    /**
     * Returns formatted column name for validation rules attribute.
     *
     * @param string $columnName Column name to format attribute name
     *
     * @return string
     */
    private function formatConstantDeclaration(string $columnName): string
    {
        $constantName = Str::upper(Str::snake($columnName));

        return "const {$constantName} = '{$columnName}';";
    }
}
