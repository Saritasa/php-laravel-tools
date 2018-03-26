<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Support\Str;
use RuntimeException;
use Saritasa\LaravelTools\CodeGenerators\GetterGenerator;
use Saritasa\LaravelTools\CodeGenerators\SetterGenerator;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\ClassPropertyObject;
use Saritasa\LaravelTools\DTO\DtoFactoryConfig;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Enums\PropertiesVisibilityTypes;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
use Saritasa\LaravelTools\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\PhpDoc\PhpDocVariableDescriptionBuilder;
use Saritasa\LaravelTools\Services\TemplateWriter;

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
     * Allows to generate getter function declaration.
     *
     * @var GetterGenerator
     */
    private $getterGenerator;

    /**
     * Allows to generate setter function declaration.
     *
     * @var SetterGenerator
     */
    private $setterGenerator;

    /**
     * DTO class builder. Allows to create Dto class for model.
     * This DTO class will contain model's attributes validation based on model's table structure.
     *
     * @param SchemaReader $schemaReader Database table information reader
     * @param TemplateWriter $templateWriter Templates files writer
     * @param IPhpTypeMapper $phpTypeMapper Storage type to PHP scalar type mapper
     * @param PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder Allows to build PHPDoc class description
     * @param PhpDocVariableDescriptionBuilder $variableDescriptionBuilder Allows to build PHPDoc for properties
     * @param GetterGenerator $getterGenerator Allows to generate getter function declaration
     * @param SetterGenerator $setterGenerator Allows to generate setter function declaration
     */
    public function __construct(
        SchemaReader $schemaReader,
        TemplateWriter $templateWriter,
        IPhpTypeMapper $phpTypeMapper,
        PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder,
        PhpDocVariableDescriptionBuilder $variableDescriptionBuilder,
        GetterGenerator $getterGenerator,
        SetterGenerator $setterGenerator
    ) {
        parent::__construct($templateWriter, $schemaReader);
        $this->phpTypeMapper = $phpTypeMapper;
        $this->phpDocClassDescriptionBuilder = $phpDocClassDescriptionBuilder;
        $this->variableDescriptionBuilder = $variableDescriptionBuilder;
        $this->getterGenerator = $getterGenerator;
        $this->setterGenerator = $setterGenerator;
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

        if ($this->config->propertiesVisibility !== PropertiesVisibilityTypes::PUBLIC) {
            foreach ($this->columns as $column) {
                $classProperties[] = new ClassPropertyObject([
                    ClassPropertyObject::NAME => $column->getName(),
                    ClassPropertyObject::TYPE => $this->phpTypeMapper->getPhpType($column->getType()),
                    ClassPropertyObject::NULLABLE => !$column->getNotnull(),
                    ClassPropertyObject::DESCRIPTION => $column->getComment(),
                    ClassPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
                ]);
            }
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
        $getters = [];
        $setters = [];
        foreach ($this->columns as $column) {
            $classProperty = new ClassPropertyObject([
                ClassPropertyObject::NAME => $column->getName(),
                ClassPropertyObject::TYPE => $this->phpTypeMapper->getPhpType($column->getType()),
                ClassPropertyObject::NULLABLE => !$column->getNotnull(),
                ClassPropertyObject::DESCRIPTION => $column->getComment(),
                ClassPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
            ]);

            $classProperties[] = $this->variableDescriptionBuilder->render($classProperty);
            $classProperties[] = "{$this->config->propertiesVisibility} \${$classProperty->name};";
            $classProperties[] = '';

            if ($this->config->withGetters) {
                $getters[] = $this->getterGenerator->render($classProperty->name, $classProperty->type);
                $getters[] = '';
            }
            if ($this->config->withSetters) {
                $setters[] = $this->setterGenerator->render($classProperty->name, $classProperty->type);
                $setters[] = '';
            }
        }
        $classPropertiesLines = array_merge($classProperties, $getters, $setters);

        $classPropertiesLines = $this->applyIndent(implode("\n", $classPropertiesLines));

        return rtrim($classPropertiesLines);
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
