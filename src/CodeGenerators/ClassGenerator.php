<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Exception;
use Saritasa\LaravelTools\CodeGenerators\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassObject;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Php class generator. Allows to build class declaration based on class details.
 */
class ClassGenerator
{
    // Template placeholders
    private const PLACEHOLDER_NAMESPACE = 'namespace';
    private const PLACEHOLDER_IMPORTS = 'imports';
    private const PLACEHOLDER_CLASS_PHP_DOC = 'classPhpDoc';
    private const PLACEHOLDER_CLASS_NAME = 'className';
    private const PLACEHOLDER_PARENT = 'parent';
    private const PLACEHOLDER_CLASS_CONTENT = 'classContent';

    /**
     * Scaffold templates writer. Takes template, fills placeholders and writes result file.
     *
     * @var TemplateWriter
     */
    private $templateWriter;

    /**
     * Code style utility. Allows to format code according to settings. Can apply valid indent to code line or code
     * block.
     *
     * @var CodeFormatter
     */
    private $codeFormatter;

    /**
     * Php comments generator. Allows to comment lines and blocks of text.
     *
     * @var CommentsGenerator
     */
    private $commentsGenerator;

    /**
     * Allows to render php-class description.
     *
     * @var PhpDocClassDescriptionBuilder
     */
    private $phpDocClassDescriptionBuilder;

    /**
     * Php function code generator. Allows to generate function declaration by parameters.
     *
     * @var FunctionGenerator
     */
    private $functionGenerator;

    /**
     * Class property generator. Allows to generate class property definition and description.
     *
     * @var ClassPropertyGenerator
     */
    private $classPropertyGenerator;

    /**
     * Namespace extractor. Allows to retrieve list of used namespaces from code and remove FQN from it.
     *
     * @var NamespaceExtractor
     */
    private $namespaceExtractor;

    /**
     * Php class generator. Allows to build class declaration based on class details.
     *
     * @param TemplateWriter $templateWriter Scaffold templates writer. Takes template, fills placeholders and writes
     *     result file
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings. Can apply
     *     valid indent to code line or code block
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text
     * @param NamespaceExtractor $namespaceExtractor Namespace extractor. Allows to retrieve list of used namespaces
     *     from code and remove FQN from it
     * @param PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder Allows to render php-class description
     * @param FunctionGenerator $functionGenerator Php function code generator. Allows to generate function declaration
     *     by parameters
     * @param ClassPropertyGenerator $classPropertyGenerator Class property generator. Allows to generate class
     *     property definition and description
     */
    public function __construct(
        TemplateWriter $templateWriter,
        CodeFormatter $codeFormatter,
        CommentsGenerator $commentsGenerator,
        NamespaceExtractor $namespaceExtractor,
        PhpDocClassDescriptionBuilder $phpDocClassDescriptionBuilder,
        FunctionGenerator $functionGenerator,
        ClassPropertyGenerator $classPropertyGenerator
    ) {
        $this->templateWriter = $templateWriter;
        $this->codeFormatter = $codeFormatter;
        $this->commentsGenerator = $commentsGenerator;
        $this->phpDocClassDescriptionBuilder = $phpDocClassDescriptionBuilder;
        $this->functionGenerator = $functionGenerator;
        $this->classPropertyGenerator = $classPropertyGenerator;
        $this->namespaceExtractor = $namespaceExtractor;
    }

    /**
     * Generates class content and returns as a string.
     *
     * @param ClassObject $classObject Class details to render
     * @param string $template Class file template content
     *
     * @return string Class declaration file content
     * @throws Exception
     */
    public function render(ClassObject $classObject, string $template): string
    {
        $this->templateWriter->setTemplateContent($template);

        return $this->templateWriter
            ->fill($this->getPlaceholders($classObject))
            ->getTemplateContent();
    }

    /**
     * Returns class constants definition.
     *
     * @param ClassObject $classObject Class details to retrieve constants information.
     *
     * @return string
     */
    private function getConstantsBlock(ClassObject $classObject): string
    {
        $result = [];

        foreach ($classObject->constants as $constant) {
            if ($constant->description) {
                $result[] = $this->commentsGenerator->block($this->codeFormatter->toSentence($constant->description));
            }
            $result[] = "{$constant->visibilityType} const {$constant->name} = {$constant->value};";
        }

        return $this->codeFormatter->linesToBlock($result);
    }

    /**
     * Returns class properties definition.
     *
     * @param ClassObject $classObject Class details to retrieve properties information.
     *
     * @return string
     */
    private function getPropertiesBlock(ClassObject $classObject): string
    {
        $result = [];

        foreach ($classObject->properties as $property) {
            if ($result) {
                $result[] = '';
            }
            $result[] = $this->classPropertyGenerator->render($property);
        }

        return $this->codeFormatter->linesToBlock($result);
    }

    /**
     * Returns class methods definition.
     *
     * @param ClassObject $classObject Class details to retrieve methods information.
     *
     * @return string
     */
    private function getMethodsBlock(ClassObject $classObject): string
    {
        $result = [];

        foreach ($classObject->methods as $method) {
            if ($result) {
                $result[] = '';
            }
            $result[] = $this->functionGenerator->render($method);
        }

        return $this->codeFormatter->linesToBlock($result);
    }

    /**
     * Returns full class body with constants, properties and methods.
     *
     * @param ClassObject $classObject Class details to generate class body
     *
     * @return string
     */
    private function getClassContent(ClassObject $classObject): string
    {
        $result = [];

        $constantsBlock = $this->getConstantsBlock($classObject);
        if ($constantsBlock) {
            $result[] = $constantsBlock;
        }

        $propertiesBlock = $this->getPropertiesBlock($classObject);
        if ($propertiesBlock) {
            if ($result) {
                $result[] = '';
            }
            $result[] = $propertiesBlock;
        }

        $methodsBlock = $this->getMethodsBlock($classObject);
        if ($methodsBlock) {
            if ($result) {
                $result[] = '';
            }
            $result[] = $methodsBlock;
        }

        return $this->codeFormatter->indentBlock($this->codeFormatter->linesToBlock($result));
    }

    /**
     * Returns placeholders values for class template.
     *
     * @param ClassObject $classObject Class object to retrieve placeholders values
     *
     * @return array
     */
    private function getPlaceholders(ClassObject $classObject): array
    {
        $classPhpDoc = $this->phpDocClassDescriptionBuilder->render(
            $classObject->description,
            $classObject->phpDocProperties
        );

        $result = [
            static::PLACEHOLDER_CLASS_PHP_DOC => $classPhpDoc,
            static::PLACEHOLDER_CLASS_NAME => $classObject->name,
            static::PLACEHOLDER_PARENT => $classObject->parent,
            static::PLACEHOLDER_CLASS_CONTENT => $this->getClassContent($classObject),
        ];

        $imports = [];

        foreach ($result as $placeholder => $value) {
            $imports = array_unique(array_merge($imports, $this->namespaceExtractor->extract($result[$placeholder])));
        }

        $result[static::PLACEHOLDER_NAMESPACE] = $classObject->namespace;
        $result[static::PLACEHOLDER_IMPORTS] = $this->namespaceExtractor->format($imports);

        return $result;
    }
}
