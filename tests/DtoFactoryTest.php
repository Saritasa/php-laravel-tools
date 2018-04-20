<?php

namespace Saritasa\LaravelTools\Tests;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\User;
use PHPUnit\Framework\TestCase;
use Saritasa\Dto;
use Saritasa\LaravelTools\CodeGenerators\CodeStyler;
use Saritasa\LaravelTools\CodeGenerators\GetterGenerator;
use Saritasa\LaravelTools\CodeGenerators\SetterGenerator;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\DtoFactoryConfig;
use Saritasa\LaravelTools\Factories\DtoFactory;
use Saritasa\LaravelTools\Mappings\DbalToPhpTypeMapper;
use Saritasa\LaravelTools\Mappings\PhpToPhpDocTypeMapper;
use Saritasa\LaravelTools\PhpDoc\PhpDocClassDescriptionBuilder;
use Saritasa\LaravelTools\PhpDoc\PhpDocSingleLinePropertyDescriptionBuilder;
use Saritasa\LaravelTools\PhpDoc\PhpDocVariableDescriptionBuilder;
use Saritasa\LaravelTools\Services\DtoService;
use Saritasa\LaravelTools\Services\TemplatesManager;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Test DTO factory.
 */
class DtoFactoryTest extends TestCase
{
    /** @var string Name of temporary created template file */
    private $testTemplateFileName = 'UnitTestsDtoFakeTemplate.tmp';

    /** @var string Name of temporary created filled template file */
    private $testResultFileName = 'UnitTestsDtoFakeTemplateResult.tmp';

    /**
     * Test setup. Prepares DTO template file.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createDtoTestTemplate();
    }

    /**
     * Test end handler. Removes all generated data.
     *
     * @return void
     */
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
     * Generates test configuration of DTO factory.
     *
     * @param array $excludedAttributes Array with attributes of model that should be removed from DTO
     * @param bool $immutable Is DTO should be immutable
     * @param bool $strictTypes Is DTO should be with strict typed getters and setters
     * @param bool $withConstants Whether constants block with attributes names should be generated
     *
     * @return DtoFactoryConfig
     */
    private function getDtoFactoryConfig(
        array $excludedAttributes = [],
        bool $immutable = false,
        bool $strictTypes = false,
        bool $withConstants = true
    ): DtoFactoryConfig {
        return new DtoFactoryConfig([
            DtoFactoryConfig::NAMESPACE => 'App\\Models\\Dto',
            DtoFactoryConfig::PARENT_CLASS_NAME => Dto::class,
            DtoFactoryConfig::CLASS_NAME => 'TestDto',
            DtoFactoryConfig::MODEL_CLASS_NAME => User::class,
            DtoFactoryConfig::RESULT_FILENAME => 'TestDto.php',
            DtoFactoryConfig::TEMPLATE_FILENAME => $this->testTemplateFileName,
            DtoFactoryConfig::EXCLUDED_ATTRIBUTES => $excludedAttributes,
            DtoFactoryConfig::IMMUTABLE => $immutable,
            DtoFactoryConfig::STRICT_TYPES => $strictTypes,
            DtoFactoryConfig::WITH_CONSTANTS => $withConstants,
        ]);
    }

    /**
     * Returns fake table information.
     *
     * @return Table
     * @throws DBALException
     */
    private function getFakeTable(): Table
    {
        $columns = [
            // Test required field
            new Column('id', Type::getType(Type::BIGINT)),
            // Test not required string field with max length
            new Column('name', Type::getType(Type::STRING), ['length' => 40, 'notNull' => false]),
            // Test dates
            new Column('birth_date', Type::getType(Type::DATE), ['comment' => 'The date when user was born']),
        ];

        $table = new Table('users', $columns, [], [], 0);

        $table->setPrimaryKey(['id']);

        return $table;
    }

    /**
     * Initializes DTO factory.
     *
     * @param Table $table Table to read details from
     *
     * @return DtoFactory
     */
    private function getDtoFactory(Table $table): DtoFactory
    {
        /**
         *  Real and mocked dependencies.
         */
        $codeStyler = new CodeStyler(new Repository());
        $phpTypeMapper = new DbalToPhpTypeMapper();
        $templateWriter = new TemplateWriter(app(Filesystem::class));
        $classDescriptionBuilder = new PhpDocClassDescriptionBuilder(
            new PhpDocSingleLinePropertyDescriptionBuilder(
                new PhpToPhpDocTypeMapper()
            )
        );
        $variableDescriptionBuilder = new PhpDocVariableDescriptionBuilder(new PhpToPhpDocTypeMapper());
        $getterGenerator = new GetterGenerator(new PhpToPhpDocTypeMapper());
        $setterGenerator = new SetterGenerator(new PhpToPhpDocTypeMapper());
        /** @var SchemaReader $schemaReader */
        $schemaReader = \Mockery::mock(SchemaReader::class)
            ->expects('getTableDetails')
            ->andReturn($table)
            ->getMock();

        return new DtoFactory(
            $schemaReader,
            $templateWriter,
            $codeStyler,
            $phpTypeMapper,
            $classDescriptionBuilder,
            $variableDescriptionBuilder,
            $getterGenerator,
            $setterGenerator
        );
    }

    /**
     * Tests build() method of DtoFactory.
     *
     * @dataProvider dtoBuildTestDataProvider
     *
     * @param DtoFactoryConfig $dtoFactoryConfig Test configuration for DTO factory
     * @param string $expectedDtoContent Expected DTO content
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testBuildMethod(
        DtoFactoryConfig $dtoFactoryConfig,
        string $expectedDtoContent
    ) {
        $table = $this->getFakeTable();

        $dtoFactory = $this->getDtoFactory($table);

        $resultFileName = $dtoFactory->configure($dtoFactoryConfig)->build();
        $resultFile = file_get_contents($resultFileName);

        $this->assertEquals($dtoFactoryConfig->resultFilename, $resultFileName);
        $this->assertEquals($expectedDtoContent, $resultFile);
    }

    /**
     * Test DTO service.
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testDtoService(): void
    {
        $table = $this->getFakeTable();
        $dtoFactory = $this->getDtoFactory($table);
        $dtoFactoryConfig = $this->getDtoFactoryConfig();

        $templatesManager = new TemplatesManager();
        $repository = new Repository([
            'laravel_tools.dto.namespace' => $dtoFactoryConfig->namespace,
            'laravel_tools.dto.parent' => $dtoFactoryConfig->parentClassName,
            'laravel_tools.dto.except' => $dtoFactoryConfig->excludedAttributes,
            'laravel_tools.dto.path' => __DIR__,
        ]);

        $dtoService = new DtoService($repository, $templatesManager, $dtoFactory);
        $resultFileName = $dtoService->generateDto(
            $dtoFactoryConfig->modelClassName,
            $dtoFactoryConfig->className,
            $dtoFactoryConfig
        );

        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . $dtoFactoryConfig->resultFilename, $resultFileName);

        unlink($resultFileName);
    }

    /**
     * Test DTO service wrong configuration exceptions.
     *
     * @dataProvider dtoServiceProvider
     *
     * @param string $missedConfig Config key that should be missed in config
     * @param string $expectedExceptionMessage Expected message that should be thrown
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testDtoServiceConfigurationChecks(
        string $missedConfig,
        string $expectedExceptionMessage
    ): void {
        $table = $this->getFakeTable();
        $dtoFactory = $this->getDtoFactory($table);
        $dtoFactoryConfig = $this->getDtoFactoryConfig();

        $templatesManager = new TemplatesManager();

        $config = [
            'laravel_tools.dto.namespace' => $dtoFactoryConfig->namespace,
            'laravel_tools.dto.parent' => $dtoFactoryConfig->parentClassName,
            'laravel_tools.dto.except' => $dtoFactoryConfig->excludedAttributes,
            'laravel_tools.dto.path' => __DIR__,
        ];
        unset($config[$missedConfig]);
        $repository = new Repository($config);

        $this->expectExceptionMessage($expectedExceptionMessage);

        $dtoService = new DtoService($repository, $templatesManager, $dtoFactory);
        $dtoService->generateDto(
            $dtoFactoryConfig->modelClassName,
            $dtoFactoryConfig->className,
            $dtoFactoryConfig
        );
    }

    /**
     * Tests set for DTO service test.
     * Contains list of attributes to check that they are missed.
     *
     * @return array
     */
    public function dtoServiceProvider(): array
    {
        return [
            'Missed namespace config' => [
                'laravel_tools.dto.namespace',
                'DTO namespace not configured',
            ],
            'Missed except config' => [
                'laravel_tools.dto.except',
                'DTO ignored attributes configuration is invalid',
            ],
        ];
    }

    /**
     * Test data for build method.
     *
     * @return array
     */
    public function dtoBuildTestDataProvider(): array
    {
        return [
            'Public variables' => [
                $this->getDtoFactoryConfig(),
                $this->getExpectedDtoContent(),
            ],
            'Public variables without ID attribute' => [
                $this->getDtoFactoryConfig(['id']),
                $this->getExpectedDtoContentWithoutId(),
            ],
            'Immutable without ID attribute' => [
                $this->getDtoFactoryConfig(['id'], true),
                $this->getExpectedImmutableithoutId(),
            ],
            'Strict typed DTO' => [
                $this->getDtoFactoryConfig(['name', 'birth_date'], false, true),
                $this->getExpectedStrictTypedDto(),
            ],
            'Immutable strict typed DTO' => [
                $this->getDtoFactoryConfig(['birth_date'], true, true),
                $this->getExpectedStrictTypedImmutableDto(),
            ],
            'DTO without constants' => [
                $this->getDtoFactoryConfig([], false, false, false),
                $this->getExpectedDtoContentWithoutConstants(),
            ],
        ];
    }

    /**
     * Create temporary file with DTO template.
     *
     * @return void
     */
    private function createDtoTestTemplate(): void
    {
        $testTemplate = <<<templateContent
<?php

namespace {{namespace}};

{{imports}}

{{classPhpDoc}}
class {{dtoClassName}} extends {{dtoParent}}
{
{{constants}}{{properties}}
}

templateContent;

        file_put_contents($this->testTemplateFileName, $testTemplate);
    }

    /**
     * Returns template that should be generated by DTO factory.
     *
     * @return string
     */
    private function getExpectedDtoContent(): string
    {
        return <<<templateContent
<?php

namespace App\Models\Dto;

use Saritasa\Dto;

/**
 * TestDto DTO.
 */
class TestDto extends Dto
{
    const ID = 'id';
    const NAME = 'name';
    const BIRTH_DATE = 'birth_date';

    /**
     * .
     *
     * @var integer
     */
    public \$id;

    /**
     * .
     *
     * @var string|null
     */
    public \$name;

    /**
     * The date when user was born.
     *
     * @var string
     */
    public \$birth_date;
}

templateContent;
    }

    /**
     * Returns template that should be generated by DTO factory without ID attribute.
     *
     * @return string
     */
    private function getExpectedDtoContentWithoutId(): string
    {
        return <<<templateContent
<?php

namespace App\Models\Dto;

use Saritasa\Dto;

/**
 * TestDto DTO.
 */
class TestDto extends Dto
{
    const NAME = 'name';
    const BIRTH_DATE = 'birth_date';

    /**
     * .
     *
     * @var string|null
     */
    public \$name;

    /**
     * The date when user was born.
     *
     * @var string
     */
    public \$birth_date;
}

templateContent;
    }

    /**
     * Returns template that should be generated without constants.
     *
     * @return string
     */
    private function getExpectedDtoContentWithoutConstants(): string
    {
        return <<<templateContent
<?php

namespace App\Models\Dto;

use Saritasa\Dto;

/**
 * TestDto DTO.
 */
class TestDto extends Dto
{
    /**
     * .
     *
     * @var integer
     */
    public \$id;

    /**
     * .
     *
     * @var string|null
     */
    public \$name;

    /**
     * The date when user was born.
     *
     * @var string
     */
    public \$birth_date;
}

templateContent;
    }

    /**
     * Returns expected immutable DTO class content.
     *
     * @return string
     */
    private function getExpectedImmutableithoutId(): string
    {
        return <<<templateContent
<?php

namespace App\Models\Dto;

use Saritasa\Dto;

/**
 * TestDto DTO.
 *
 * @property-read string|null \$name
 * @property-read string \$birth_date The date when user was born
 */
class TestDto extends Dto
{
    const NAME = 'name';
    const BIRTH_DATE = 'birth_date';

    /**
     * .
     *
     * @var string|null
     */
    protected \$name;

    /**
     * The date when user was born.
     *
     * @var string
     */
    protected \$birth_date;
}

templateContent;
    }

    /**
     * Returns expected strict typed DTO class content.
     *
     * @return string
     */
    private function getExpectedStrictTypedDto(): string
    {
        return <<<templateContent
<?php

namespace App\Models\Dto;

use Saritasa\Dto;

/**
 * TestDto DTO.
 *
 * @property integer \$id
 */
class TestDto extends Dto
{
    const ID = 'id';

    /**
     * .
     *
     * @var integer
     */
    protected \$id;

    /**
     * Get id attribute value.
     *
     * @return integer
     */
    public function getId(): int
    {
        return \$this->id;
    }

    /**
     * Set id attribute value.
     *
     * @param integer \$id New attribute value
     *
     * @return void
     */
    public function setId(int \$id): void
    {
        \$this->id = \$id;
    }
}

templateContent;
    }

    /**
     * Returns expected strict typed immutable DTO class content.
     *
     * @return string
     */
    private function getExpectedStrictTypedImmutableDto(): string
    {
        return <<<templateContent
<?php

namespace App\Models\Dto;

use Saritasa\Dto;

/**
 * TestDto DTO.
 *
 * @property-read integer \$id
 * @property-read string|null \$name
 */
class TestDto extends Dto
{
    const ID = 'id';
    const NAME = 'name';

    /**
     * .
     *
     * @var integer
     */
    protected \$id;

    /**
     * .
     *
     * @var string|null
     */
    protected \$name;

    /**
     * Get id attribute value.
     *
     * @return integer
     */
    public function getId(): int
    {
        return \$this->id;
    }

    /**
     * Get name attribute value.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return \$this->name;
    }

    /**
     * Set id attribute value.
     *
     * @param integer \$id New attribute value
     *
     * @return void
     */
    protected function setId(int \$id): void
    {
        \$this->id = \$id;
    }

    /**
     * Set name attribute value.
     *
     * @param string|null \$name New attribute value
     *
     * @return void
     */
    protected function setName(?string \$name): void
    {
        \$this->name = \$name;
    }
}

templateContent;
    }
}
