<?php

namespace Saritasa\LaravelTools\Factories;

use Exception;
use Illuminate\Support\Str;
use RuntimeException;
use Saritasa\Exceptions\NotImplementedException;
use Saritasa\LaravelTools\CodeGenerators\ClassPropertyGenerator;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\GetterGenerator;
use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\CodeGenerators\SetterGenerator;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\Configs\DtoFactoryConfig;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassPhpDocPropertyObject;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassPropertyObject;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Mappings\IPhpTypeMapper;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * DTO class builder. Allows to create Dto class for model.
 * This DTO class will contain model's attributes validation based on model's table structure.
 */
class DtoFactory extends ModelBasedClassFactory
{
    protected const PLACEHOLDER_NAMESPACE = 'namespace';
    protected const PLACEHOLDER_IMPORTS = 'imports';
    protected const PLACEHOLDER_DTO_CLASS_NAME = 'dtoClassName';
    protected const PLACEHOLDER_CLASS_PHP_DOC = 'classPhpDoc';
    protected const PLACEHOLDER_DTO_CONSTANTS = 'constants';
    protected const PLACEHOLDER_DTO_PROPERTIES = 'properties';
    protected const PLACEHOLDER_DTO_PARENT = 'dtoParent';

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
     * @var ClassPropertyGenerator
     */
    private $classPropertyDescriptionBuilder;

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
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings
     * @param IPhpTypeMapper $phpTypeMapper Storage type to PHP scalar type mapper
     * @param PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder Allows to build PHPDoc class description
     * @param \Saritasa\LaravelTools\CodeGenerators\ClassPropertyGenerator $variableDescriptionBuilder Allows to build PHPDoc for properties
     * @param GetterGenerator $getterGenerator Allows to generate getter function declaration
     * @param SetterGenerator $setterGenerator Allows to generate setter function declaration
     */
    public function __construct(
        SchemaReader $schemaReader,
        TemplateWriter $templateWriter,
        CodeFormatter $codeFormatter,
        IPhpTypeMapper $phpTypeMapper,
        PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder,
        ClassPropertyGenerator $variableDescriptionBuilder,
        GetterGenerator $getterGenerator,
        SetterGenerator $setterGenerator
    ) {
        parent::__construct($templateWriter, $schemaReader, $codeFormatter);
        $this->phpTypeMapper = $phpTypeMapper;
        $this->phpDocClassDescriptionBuilder = $phpDocClassDescriptionBuilder;
        $this->classPropertyDescriptionBuilder = $variableDescriptionBuilder;
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
            static::PLACEHOLDER_DTO_CONSTANTS => $this->config->withConstants
                ? $this->getConstantsBlock() . "\n\n"
                : '',
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
     * @throws NotImplementedException
     */
    private function getClassDocBlock(): string
    {
        $classProperties = [];

        if ($this->config->immutable || $this->config->strictTypes) {
            $propertiesAccessType = PhpDocPropertyAccessTypes::READ_AND_WRITE;

            if ($this->config->immutable) {
                $propertiesAccessType = PhpDocPropertyAccessTypes::READ;
            }

            foreach ($this->columns as $column) {
                $classProperties[] = new ClassPhpDocPropertyObject([
                    ClassPhpDocPropertyObject::NAME => $column->getName(),
                    ClassPhpDocPropertyObject::TYPE => $this->phpTypeMapper->getPhpType($column->getType()),
                    ClassPhpDocPropertyObject::NULLABLE => !$column->getNotnull(),
                    ClassPhpDocPropertyObject::DESCRIPTION => $column->getComment(),
                    ClassPhpDocPropertyObject::ACCESS_TYPE => $propertiesAccessType,
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
            $classConstants[] = $this->formatConstantDeclaration($column->getName());
        }

        return $this->codeFormatter->indentBlock(implode("\n", $classConstants));
    }

    /**
     * Format DTO constants that are represents available attributes names.
     *
     * @return string
     * @throws NotImplementedException
     */
    private function getPropertiesBlock(): string
    {
        $classProperties = [];
        $getters = [];
        $setters = [];
        foreach ($this->columns as $column) {
            $propertyVisibility = ($this->config->immutable || $this->config->strictTypes)
                ? ClassMemberVisibilityTypes::PROTECTED
                : ClassMemberVisibilityTypes::PUBLIC;
            $classProperty = new ClassPropertyObject([
                ClassPropertyObject::NAME => $column->getName(),
                ClassPropertyObject::TYPE => $this->phpTypeMapper->getPhpType($column->getType()),
                ClassPropertyObject::NULLABLE => !$column->getNotnull(),
                ClassPropertyObject::DESCRIPTION => $column->getComment(),
                ClassPropertyObject::VISIBILITY_TYPE => $propertyVisibility,
            ]);

            $classProperties[] = $this->classPropertyDescriptionBuilder->render($classProperty);
            $classProperties[] = '';

            if ($this->config->strictTypes) {
                $getters[] = $this->getterGenerator->render(
                    $classProperty->name,
                    $classProperty->type,
                    ClassMemberVisibilityTypes::PUBLIC,
                    $classProperty->nullable
                );
                $getters[] = '';

                $setterVisibility = $this->config->immutable
                    ? ClassMemberVisibilityTypes::PROTECTED
                    : ClassMemberVisibilityTypes::PUBLIC;
                $setters[] = $this->setterGenerator->render(
                    $classProperty->name,
                    $classProperty->type,
                    $setterVisibility,
                    $classProperty->nullable
                );
                $setters[] = '';
            }
        }
        $classPropertiesLines = array_merge($classProperties, $getters, $setters);

        $classPropertiesLines = $this->codeFormatter->indentBlock(implode("\n", $classPropertiesLines));

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
