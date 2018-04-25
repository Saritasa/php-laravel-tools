<?php

namespace Saritasa\LaravelTools\Tests;

class NamespaceExtractorTest extends LaravelToolsTestsHelpers
{
    public function testFormat(): void
    {
        $namespaceExtractor = $this->getNamespaceExtractor();

        // Single import
        $expected = "use App\\Models\\User;";
        $actual = $namespaceExtractor->format(['App\\Models\\User']);

        $this->assertEquals($expected, $actual);

        // No imports
        $expected = "";
        $actual = $namespaceExtractor->format([]);

        $this->assertEquals($expected, $actual);

        // Few imports
        $expected = "use App\\Models\\User;\n" .
            "use Carbon\\Carbon;";
        $actual = $namespaceExtractor->format(['App\\Models\\User', 'Carbon\\Carbon']);

        $this->assertEquals($expected, $actual);
    }

    /**
     *
     * @dataProvider extractTestSet
     *
     * @param string $testString
     * @param string $expectedStringWithoutNamespace
     * @param array $expectedExtractedNamespaces
     *
     * @return void
     */
    public function testExtract(
        string $testString,
        string $expectedStringWithoutNamespace,
        array $expectedExtractedNamespaces
    ) {
        $extractedNamespaces = $this->getNamespaceExtractor()->extract($testString);
        $this->assertEquals($expectedStringWithoutNamespace, $testString);
        $this->assertEquals(sort($expectedExtractedNamespaces), sort($extractedNamespaces));
    }

    public function extractTestSet(): array
    {
        return [
            'One namespace' => [
                'return \\App\\Models\\User::get();',
                'return User::get();',
                ['App\\Models\\User'],
            ],
            'One short namespace' => [
                'throw new \Exception();',
                'throw new Exception();',
                ['Exception'],
            ],
            'Two identical namespaces' => [
                'return [\\App\\Models\\User::first(), \\App\\Models\\User::last()];',
                'return [User::first(), User::last()];',
                ['App\\Models\\User'],
            ],
            'Two different namespaces' => [
                'return [\\App\\Models\\User::first(), \\App\\Models\\Role::last()];',
                'return [User::first(), Role::last()];',
                ['App\\Models\\User', 'App\\Models\\Role'],
            ],
            'No namespaces different namespaces' => [
                'return $a + $b;',
                'return $a + $b;',
                [],
            ],
        ];
    }
}
