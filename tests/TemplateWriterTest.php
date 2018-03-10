<?php

namespace Saritasa\LaravelTools\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelTools\Services\TemplateWriter;
use UnexpectedValueException;

/**
 * Test template writer service.
 */
class TemplateWriterTest extends TestCase
{
    /** @var TemplateWriter */
    private $templateWriter;

    /** @var string Name of temporary created template file */
    private $testTemplateFileName = 'UnitTestsFakeTemplate.tmp';

    /** @var string Name of temporary created filled template file */
    private $testResultFileName = 'UnitTestsFakeTemplateResult.tmp';

    protected function setUp()
    {
        parent::setUp();
        // Create necessary file with template content
        file_put_contents($this->testTemplateFileName, $this->getTestTemplateContent());

        $this->templateWriter = new TemplateWriter(app(Filesystem::class));
    }

    protected function tearDown()
    {
        parent::tearDown();
        // Clear created files after test execution
        $filesToRemove = [
            $this->testTemplateFileName,
            $this->testResultFileName,
        ];
        foreach ($filesToRemove as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * Test template content.
     *
     * @return string
     */
    private function getTestTemplateContent(): string
    {
        return 'quick {{FOX_COLOR}} fox';
    }

    /**
     * Test that template writer disallow to return empty template content.
     *
     * @return void
     */
    public function testGetterValidation()
    {
        $this->expectException(UnexpectedValueException::class);

        $this->templateWriter->getTemplateContent();
    }

    /**
     * Test that template writer returns same content that was set.
     *
     * @return void
     */
    public function testContentGetter()
    {
        $testTemplateContent = $this->getTestTemplateContent();
        $this->templateWriter->setTemplateContent($testTemplateContent);
        $retrievedTemplate = $this->templateWriter->getTemplateContent();

        $this->assertEquals($testTemplateContent, $retrievedTemplate);
    }

    /**
     * Test that template content from file successfully read and throws an exception when file not readable.
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function testFileReading()
    {
        $actualContent = $this->templateWriter->take($this->testTemplateFileName)->getTemplateContent();
        $this->assertEquals($this->getTestTemplateContent(), $actualContent);

        $this->expectException(FileNotFoundException::class);
        $this->templateWriter->take('some_not_existing_file.ever');
    }

    /**
     * Test that template writer successfully fills given placeholders and
     * restricts fill not existing in template placeholder.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function testFillFunction()
    {
        $placeholders = [
            'FOX_COLOR' => 'brown',
        ];

        $actualContent = $this->templateWriter
            ->take($this->testTemplateFileName)
            ->fill($placeholders)
            ->getTemplateContent();
        $expected = 'quick brown fox';

        $this->assertEquals($expected, $actualContent);

        $this->expectExceptionMessage('Placeholder {{NOT_EXISTING_PLACEHOLDER}} not found in template');

        $this->templateWriter
            ->take($this->testTemplateFileName)
            ->fill(['NOT_EXISTING_PLACEHOLDER' => 'abc']);
    }

    /**
     * Test that template writer successfully writes filled template content and restricts write template content with
     * not filled placeholders.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function testWriteFunction()
    {
        $placeholders = [
            'FOX_COLOR' => 'brown',
        ];

        $this->templateWriter
            ->take($this->testTemplateFileName)
            ->fill($placeholders)
            ->write($this->testResultFileName);

        $expected = 'quick brown fox';
        $actual = file_get_contents($this->testResultFileName);

        $this->assertEquals($expected, $actual);

        $this->expectExceptionMessage('Template placeholder(s) [FOX_COLOR] not filled. Did You fill() placeholders?');
        $this->templateWriter
            ->take($this->testTemplateFileName)
            ->fill([])
            ->write($this->testResultFileName);
    }
}
