<?php

namespace Saritasa\LaravelTools\CodeGenerators;

use Saritasa\LaravelTools\DTO\PhpClasses\ClassPropertyObject;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;

/**
 * PhpDoc for variable builder. Allows to generate PhpDoc variable description block.
 */
class ClassPropertyGenerator
{
    /**
     * Php scalar type to PhpDoc scalar type mapper.
     *
     * @var PhpToPhpDocTypeMapper
     */
    private $phpToPhpDocTypeMapper;

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
     * PhpDoc for variable builder. Allows to generate PhpDoc variable description block.
     *
     * @param PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper Php scalar type to PhpDoc scalar type mapper
     * @param CommentsGenerator $commentsGenerator Php comments generator. Allows to comment lines and blocks of text
     * @param CodeFormatter $codeFormatter Code style utility. Allows to format code according to settings. Can apply
     *     valid indent to code line or code block
     */
    public function __construct(
        PhpToPhpDocTypeMapper $phpToPhpDocTypeMapper,
        CommentsGenerator $commentsGenerator,
        CodeFormatter $codeFormatter
    ) {
        $this->phpToPhpDocTypeMapper = $phpToPhpDocTypeMapper;
        $this->commentsGenerator = $commentsGenerator;
        $this->codeFormatter = $codeFormatter;
    }

    /**
     * Return PhpDoc variable description block.
     *
     * @param ClassPropertyObject $classProperty Class property details
     *
     * @return string
     */
    public function render(ClassPropertyObject $classProperty): string
    {
        $nullableType = $classProperty->nullable
            ? '|null'
            : '';
        $phpDocType = $this->phpToPhpDocTypeMapper->getPhpDocType($classProperty->type);

        $description = [];
        $description[] = $this->codeFormatter->toSentence($classProperty->description);
        $description[] = '';
        $description[] = "@var {$phpDocType}{$nullableType}";

        $result = [];
        $result[] = $this->commentsGenerator->block($this->codeFormatter->linesToBlock($description));
        $result[] = "{$classProperty->visibilityType} \${$classProperty->name};";

        return $this->codeFormatter->linesToBlock($result);
    }
}
