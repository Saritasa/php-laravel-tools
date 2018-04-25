<?php

namespace Saritasa\LaravelTools\CodeGenerators\PhpDoc;

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
     * Allows to render php-class description.
     *
     * @param PhpDocSingleLinePropertyDescriptionBuilder $phpDocClassPropertyBuilder PhpDoc property renderer
     */
    public function __construct(PhpDocSingleLinePropertyDescriptionBuilder $phpDocClassPropertyBuilder)
    {
        $this->phpDocClassPropertyBuilder = $phpDocClassPropertyBuilder;
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
        $result[] = '/**';
        $result[] = " * {$classDescription}.";
        if ($classProperties) {
            $result[] = ' *';
        }
        foreach ($classProperties as $classProperty) {
            $result[] = $this->phpDocClassPropertyBuilder->render($classProperty);
        }
        $result[] = ' */';

        return implode("\n", $result);
    }
}
