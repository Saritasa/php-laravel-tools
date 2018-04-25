<?php

namespace Saritasa\LaravelTools\Tests;

use Carbon\Carbon;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\User;
use Saritasa\LaravelTools\CodeGenerators\CodeFormatter;
use Saritasa\LaravelTools\CodeGenerators\NamespaceExtractor;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\Configs\FormRequestFactoryConfig;
use Saritasa\LaravelTools\Factories\FormRequestFactory;
use Saritasa\LaravelTools\Mappings\DbalToLaravelValidationTypeMapper;
use Saritasa\LaravelTools\Mappings\DbalToPhpTypeMapper;
use Saritasa\LaravelTools\Rules\RuleBuilder;
use Saritasa\LaravelTools\Rules\StringValidationRulesDictionary;
use Saritasa\LaravelTools\Services\FormRequestService;
use Saritasa\LaravelTools\Services\TemplatesManager;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Test form request factory.
 */
class FormRequestFactoryTest extends LaravelToolsTestsHelpers
{
    /** @var string Name of temporary created template file */
    private $testTemplateFileName = 'UnitTestsFakeTemplate.tmp';

    /** @var string Name of temporary created filled template file */
    private $testResultFileName = 'UnitTestsFakeTemplateResult.tmp';

    /**
     * Test setup. Prepares form request template file.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createFormRequestTestTemplate();
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
     * Generates test configuration of form request factory.
     *
     * @param bool $suggestAttributeNames Suggest that attribute names constants in model exists
     * @param array $excludedAttributes Array with attributes of model that should be removed from form request
     *
     * @return \Saritasa\LaravelTools\DTO\Configs\FormRequestFactoryConfig
     */
    private function getFormRequestFactoryConfig(
        bool $suggestAttributeNames = false,
        array $excludedAttributes = []
    ): FormRequestFactoryConfig {
        return new FormRequestFactoryConfig([
            FormRequestFactoryConfig::NAMESPACE => 'App\\FormRequests',
            FormRequestFactoryConfig::PARENT_CLASS_NAME => 'App\\FormRequests\\Request',
            FormRequestFactoryConfig::CLASS_NAME => 'TestFormRequest',
            FormRequestFactoryConfig::MODEL_CLASS_NAME => User::class,
            FormRequestFactoryConfig::RESULT_FILENAME => 'TestFormRequest.php',
            FormRequestFactoryConfig::TEMPLATE_FILENAME => $this->testTemplateFileName,
            FormRequestFactoryConfig::EXCLUDED_ATTRIBUTES => $excludedAttributes,
            FormRequestFactoryConfig::SUGGEST_ATTRIBUTE_NAMES_CONSTANTS => $suggestAttributeNames,
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
            // Test boolean field with comment
            new Column('active', Type::getType(Type::BOOLEAN), ['comment' => 'Is this user active or not']),
            // Test foreign key
            new Column('role_id', Type::getType(Type::BIGINT)),
        ];

        $foreignKey = new ForeignKeyConstraint(['role_id'], 'roles', ['id']);

        $table = new Table('users', $columns, [], [$foreignKey], 0);

        $table->setPrimaryKey(['id']);

        return $table;
    }

    /**
     * Initializes form request factory.
     *
     * @param Table $table Table to read details from
     *
     * @return FormRequestFactory
     */
    private function getFormRequestFactory(Table $table): FormRequestFactory
    {
        /**
         * Real and mocked dependencies.
         */
        $codeFormatter = new CodeFormatter(new Repository());
        $ruleBuilder = new RuleBuilder(new StringValidationRulesDictionary(), new DbalToLaravelValidationTypeMapper());
        $phpTypeMapper = new DbalToPhpTypeMapper();
        $templateWriter = new TemplateWriter(app(Filesystem::class));
        $classDescriptionBuilder = $this->getPhpDocClassDescriptionBuilder();
        $namespaceExtractor = new NamespaceExtractor($this->getCodeFormatter());
        /** @var SchemaReader $schemaReader */
        $schemaReader = \Mockery::mock(SchemaReader::class)
            ->expects('getTableDetails')
            ->andReturn($table)
            ->getMock();

        return new FormRequestFactory(
            $schemaReader,
            $templateWriter,
            $namespaceExtractor,
            $codeFormatter,
            $ruleBuilder,
            $phpTypeMapper,
            $classDescriptionBuilder
        );
    }

    /**
     * Tests build() method of FormRequestFactory.
     *
     * @dataProvider formRequestBuildTestDataProvider
     *
     * @param FormRequestFactoryConfig $formRequestFactoryConfig Test configuration for form request factory
     * @param string $expectedFormRequestContent Expected form request content
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testBuildMethod(
        FormRequestFactoryConfig $formRequestFactoryConfig,
        string $expectedFormRequestContent
    ) {
        $table = $this->getFakeTable();

        $formRequestFactory = $this->getFormRequestFactory($table);

        $resultFileName = $formRequestFactory->configure($formRequestFactoryConfig)->build();
        $resultFile = file_get_contents($resultFileName);

        $this->assertEquals($formRequestFactoryConfig->resultFilename, $resultFileName);
        $this->assertEquals($expectedFormRequestContent, $resultFile);
    }

    /**
     * Tests build() method with empty model class.
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testBuildMethodWithEmptyModelClass()
    {
        $table = $this->getFakeTable();
        $formRequestFactory = $this->getFormRequestFactory($table);

        $formRequestFactoryConfig = $this->getFormRequestFactoryConfig();
        $formRequestFactoryConfig->modelClassName = '';

        $this->expectExceptionMessage('Model class not configured');

        $formRequestFactory->configure($formRequestFactoryConfig)->build();;
    }

    /**
     * Tests build() method with wrong model class.
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testBuildMethodWithWrongModelClass()
    {
        $table = $this->getFakeTable();
        $formRequestFactory = $this->getFormRequestFactory($table);

        $formRequestFactoryConfig = $this->getFormRequestFactoryConfig();
        $formRequestFactoryConfig->modelClassName = Carbon::class;

        $this->expectExceptionMessage('Class [Carbon\Carbon] is not a valid Model class name');

        $formRequestFactory->configure($formRequestFactoryConfig)->build();;
    }

    /**
     * Test form request service.
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testFormRequestService(): void
    {
        $table = $this->getFakeTable();
        $formRequestFactory = $this->getFormRequestFactory($table);
        $formRequestFactoryConfig = $this->getFormRequestFactoryConfig();

        $templatesManager = new TemplatesManager();
        $repository = new Repository([
            'laravel_tools.form_requests.namespace' => $formRequestFactoryConfig->namespace,
            'laravel_tools.form_requests.parent' => $formRequestFactoryConfig->parentClassName,
            'laravel_tools.form_requests.except' => $formRequestFactoryConfig->excludedAttributes,
            'laravel_tools.form_requests.path' => __DIR__,
        ]);

        $formRequestService = new FormRequestService($repository, $templatesManager, $formRequestFactory);
        $resultFileName = $formRequestService->generateFormRequest(
            $formRequestFactoryConfig->modelClassName,
            $formRequestFactoryConfig->className
        );

        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . $formRequestFactoryConfig->resultFilename, $resultFileName);

        unlink($resultFileName);
    }

    /**
     * Test form request service wrong configuration exceptions.
     *
     * @dataProvider formRequestServiceProvider
     *
     * @param string $missedConfig Config key that should be missed in config
     * @param string $expectedExceptionMessage Expected message that should be thrown
     *
     * @return void
     * @throws DBALException
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function testFormRequestServiceConfigurationChecks(
        string $missedConfig,
        string $expectedExceptionMessage
    ): void {
        $table = $this->getFakeTable();
        $formRequestFactory = $this->getFormRequestFactory($table);
        $formRequestFactoryConfig = $this->getFormRequestFactoryConfig();

        $templatesManager = new TemplatesManager();

        $config = [
            'laravel_tools.form_requests.namespace' => $formRequestFactoryConfig->namespace,
            'laravel_tools.form_requests.parent' => $formRequestFactoryConfig->parentClassName,
            'laravel_tools.form_requests.except' => $formRequestFactoryConfig->excludedAttributes,
            'laravel_tools.form_requests.path' => __DIR__,
        ];
        unset($config[$missedConfig]);
        $repository = new Repository($config);

        $this->expectExceptionMessage($expectedExceptionMessage);

        $formRequestService = new FormRequestService($repository, $templatesManager, $formRequestFactory);
        $formRequestService->generateFormRequest(
            $formRequestFactoryConfig->modelClassName,
            $formRequestFactoryConfig->className
        );
    }

    /**
     * Tests set for form request service test.
     * Contains list of attributes to check that they are missed.
     *
     * @return array
     */
    public function formRequestServiceProvider(): array
    {
        return [
            'Missed namespace config' => [
                'laravel_tools.form_requests.namespace',
                'Form request namespace not configured',
            ],
            'Missed parent config' => [
                'laravel_tools.form_requests.parent',
                'Form request parent class name not configured',
            ],
            'Missed except config' => [
                'laravel_tools.form_requests.except',
                'Form request ignored attributes configuration is invalid',
            ],
        ];
    }

    /**
     * Test data for build method.
     *
     * @return array
     */
    public function formRequestBuildTestDataProvider(): array
    {
        return [
            'string attribute names' => [
                $this->getFormRequestFactoryConfig(false),
                $this->getExpectedFormRequestContent(),
            ],
            'string attribute names without ID' => [
                $this->getFormRequestFactoryConfig(false, ['id']),
                $this->getExpectedFormRequestContentWithoutIdAttribute(),
            ],
            'constant attribute names' => [
                $this->getFormRequestFactoryConfig(true),
                $this->getExpectedFormRequestContentWithConstants(),
            ],
        ];
    }

    /**
     * Create temporary file with form request template.
     *
     * @return void
     */
    private
    function createFormRequestTestTemplate(): void
    {
        $testTemplate = <<<templateContent
<?php

namespace {{namespace}};

{{imports}}

{{classPhpDoc}}
class {{formRequestClassName}} extends {{formRequestParent}}
{
    /**
     * Rules that should be applied to validate request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            {{rules}}
        ];
    }
}

templateContent;

        file_put_contents($this->testTemplateFileName, $testTemplate);
    }

    /**
     * Returns template that should be generated by form request factory.
     *
     * @return string
     */
    private
    function getExpectedFormRequestContent(): string
    {
        return <<<templateContent
<?php

namespace App\FormRequests;

use App\FormRequests\Request;

/**
 * TestFormRequest form request.
 *
 * @property-read integer \$id
 * @property-read integer \$role_id
 * @property-read string|null \$name
 * @property-read boolean \$active Is this user active or not
 */
class TestFormRequest extends Request
{
    /**
     * Rules that should be applied to validate request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'role_id' => 'required|exists:roles,id|integer',
            'name' => 'nullable|string|max:40',
            'active' => 'required|boolean'
        ];
    }
}

templateContent;
    }

    /**
     * Returns template that should be generated by form request factory without ID attribute.
     *
     * @return string
     */
    private
    function getExpectedFormRequestContentWithoutIdAttribute(): string
    {
        return <<<templateContent
<?php

namespace App\FormRequests;

use App\FormRequests\Request;

/**
 * TestFormRequest form request.
 *
 * @property-read integer \$role_id
 * @property-read string|null \$name
 * @property-read boolean \$active Is this user active or not
 */
class TestFormRequest extends Request
{
    /**
     * Rules that should be applied to validate request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'role_id' => 'required|exists:roles,id|integer',
            'name' => 'nullable|string|max:40',
            'active' => 'required|boolean'
        ];
    }
}

templateContent;
    }

    /**
     * Returns template with constants as attribute names that should be generated by form request factory.
     *
     * @return string
     */
    private
    function getExpectedFormRequestContentWithConstants(): string
    {
        return <<<templateContent
<?php

namespace App\FormRequests;

use App\FormRequests\Request;
use Illuminate\Foundation\Auth\User;

/**
 * TestFormRequest form request.
 *
 * @property-read integer \$id
 * @property-read integer \$role_id
 * @property-read string|null \$name
 * @property-read boolean \$active Is this user active or not
 */
class TestFormRequest extends Request
{
    /**
     * Rules that should be applied to validate request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            User::ID => 'required|integer',
            User::ROLE_ID => 'required|exists:roles,id|integer',
            User::NAME => 'nullable|string|max:40',
            User::ACTIVE => 'required|boolean'
        ];
    }
}

templateContent;
    }
}
