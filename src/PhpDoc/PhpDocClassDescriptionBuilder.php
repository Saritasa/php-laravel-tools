<?php

namespace Saritasa\LaravelTools\PhpDoc;

use Saritasa\LaravelTools\DTO\ClassPropertyObject;

/**
 * Allows to render php-class description.
 */
class PhpDocClassDescriptionBuilder
{
    /**
     * PhpDoc property renderer.
     *
     * @var PhpDocPropertyBuilder
     */
    private $phpDocPropertyBuilder;

    /**
     * Allows to render php-class description.
     *
     * @param PhpDocPropertyBuilder $phpDocPropertyBuilder PhpDoc property renderer
     */
    public function __construct(PhpDocPropertyBuilder $phpDocPropertyBuilder)
    {
        $this->phpDocPropertyBuilder = $phpDocPropertyBuilder;
    }

    /**
     * Renders class PHPDoc block
     *
     * @param string $classDescription Class description
     * @param ClassPropertyObject[] $classProperties Class properties
     *
     * @return string
     */
    public function render(string $classDescription, array $classProperties): string
    {
        $result = [];
        $result[] = '/**';
        $result[] = ' *';
        $result[] = " * {$classDescription}";
        foreach ($classProperties as $classProperty) {
            $result[] = $this->phpDocPropertyBuilder->render($classProperty);
        }
        $result[] = ' */';

        return implode("\n", $result);
    }
}
