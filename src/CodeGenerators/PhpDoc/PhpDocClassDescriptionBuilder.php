<?php

namespace Saritasa\LaravelTools\CodeGenerators\PhpDoc;

use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\CommentsGenerator;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassPhpDocPropertyObject;

/**
 * Allows to render php-class description.
 */
class PhpDocClassDescriptionBuilder
{
    /**
     * PhpDoc property renderer.
     *
     * @var PhpDocSingleLinePropertyDescriptionBuilder
     */
    private $phpDocClassPropertyBuilder;

    /**
     * Php comments generator. Allows to comment lines and blocks of text.
     *
     * @var CommentsGenerator
     */
    private $commentsGenerator;

    /**
     * Code style utility. Allows to format code according to settings. Can apply valid indent to code line or code
     * block.
     *
     * @var CodeFormatter
     */
    private $codeFormatter;

    /**
     * Allows to render php-class description.
     *
     * @param PhpDocSingleLinePropertyDescriptionBuilder $phpDocClassPropertyBuilder PhpDoc property renderer
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text.
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings. Can apply
     *     valid indent to code line or code block
     */
    public function __construct(
        PhpDocSingleLinePropertyDescriptionBuilder $phpDocClassPropertyBuilder,
        CommentsGenerator $commentsGenerator,
        CodeFormatter $codeFormatter
    ) {
        $this->phpDocClassPropertyBuilder = $phpDocClassPropertyBuilder;
        $this->commentsGenerator = $commentsGenerator;
        $this->codeFormatter = $codeFormatter;
    }

    /**
     * Renders class PHPDoc block
     *
     * @param string $classDescription Class description
     * @param ClassPhpDocPropertyObject[] $classProperties Class properties
     *
     * @return string
     */
    public function render(string $classDescription, array $classProperties): string
    {
        $result = [];
        $result[] = "{$classDescription}.";
        if ($classProperties) {
            $result[] = '';
        }
        foreach ($classProperties as $classProperty) {
            $result[] = $this->phpDocClassPropertyBuilder->render($classProperty);
        }

        return $this->commentsGenerator->block($this->codeFormatter->linesToBlock($result));
    }
}
