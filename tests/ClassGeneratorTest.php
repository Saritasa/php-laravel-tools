<?php

namespace Saritasa\LaravelTools\Tests;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Saritasa\LaravelTools\CodeGenerators\ClassGenerator;
use Saritasa\LaravelTools\CodeGenerators\NamespaceExtractor;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassConstantObject;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassObject;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassPhpDocPropertyObject;
use Saritasa\LaravelTools\DTO\PhpClasses\ClassPropertyObject;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionObject;
use Saritasa\LaravelTools\DTO\PhpClasses\FunctionParameterObject;
use Saritasa\LaravelTools\Enums\ClassMemberVisibilityTypes;
use Saritasa\LaravelTools\Enums\PhpDocPropertyAccessTypes;
use Saritasa\LaravelTools\Services\TemplateWriter;

class ClassGeneratorTest extends LaravelToolsTestsHelpers
{
    private $classTemplate = <<<'CLASS_TEMPLATE'
<?php

namespace {{namespace}};

{{imports}}

{{classPhpDoc}}
class {{className}} extends {{parent}}
{
{{classContent}}
}

CLASS_TEMPLATE;

    /**
     * @return void
     * @throws Exception
     */
    public function testRender(): void
    {
        $classGenerator = new ClassGenerator(
            new TemplateWriter(app(Filesystem::class)),
            $this->getCodeFormatter(),
            $this->getCommentsGenerator(),
            new NamespaceExtractor($this->getCodeFormatter()),
            $this->getPhpDocClassDescriptionBuilder(),
            $this->getFunctionGenerator(),
            $this->getClassPropertyGenerator()
        );

        // Simple class generation
        $classObject = new ClassObject([
            ClassObject::NAME => 'UsersApiController',
            ClassObject::NAMESPACE => 'App\\Http\\Controllers\\Api',
            ClassObject::PARENT => 'AppApiController',
            ClassObject::DESCRIPTION => 'Controller to handle users-related API requests',
            ClassObject::PROPERTIES => [],
            ClassObject::PHPDOC_PROPERTIES => [],
            ClassObject::CONSTANTS => [],
            ClassObject::METHODS => [],
        ]);

        $actual = $classGenerator->render($classObject, $this->classTemplate);
        $expected = <<<'EXPECTED'
<?php

namespace App\Http\Controllers\Api;



/**
 * Controller to handle users-related API requests.
 */
class UsersApiController extends AppApiController
{

}

EXPECTED;

        $this->assertEquals($expected, $actual);

        // Reach class generation
        $classObject = new ClassObject([
            ClassObject::NAME => 'UsersApiController',
            ClassObject::NAMESPACE => 'App\\Http\\Controllers\\Api',
            ClassObject::PARENT => 'AppApiController',
            ClassObject::DESCRIPTION => 'Controller to handle users-related API requests',
            ClassObject::PHPDOC_PROPERTIES => [
                new ClassPhpDocPropertyObject([
                    ClassPhpDocPropertyObject::NAME => 'user',
                    ClassPhpDocPropertyObject::TYPE => '\\App\\Models\\User',
                    ClassPhpDocPropertyObject::NULLABLE => true,
                    ClassPhpDocPropertyObject::DESCRIPTION => 'Authenticated user',
                    ClassPhpDocPropertyObject::ACCESS_TYPE => PhpDocPropertyAccessTypes::READ,
                ]),
            ],
            ClassObject::CONSTANTS => [
                new ClassConstantObject([
                    ClassConstantObject::NAME => 'MAX_CONSTANTS_PER_CLASS',
                    ClassConstantObject::DESCRIPTION => 'How many constants we see in this class',
                    ClassConstantObject::VALUE => '1',
                    ClassConstantObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PRIVATE,
                ]),
            ],
            ClassObject::PROPERTIES => [
                new ClassPropertyObject([
                    ClassPropertyObject::NAME => 'startedAt',
                    ClassPropertyObject::TYPE => '\\Carbon\\Carbon',
                    ClassPropertyObject::NULLABLE => true,
                    ClassPropertyObject::DESCRIPTION => 'When request was started',
                    ClassPropertyObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
                ]),
                new ClassPropertyObject([
                    ClassPropertyObject::NAME => 'finishedAt',
                    ClassPropertyObject::TYPE => '\\Carbon\\Carbon',
                    ClassPropertyObject::NULLABLE => true,
                    ClassPropertyObject::DESCRIPTION => 'When request was ended',
                    ClassPropertyObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
                ]),
            ],
            ClassObject::METHODS => [
                new FunctionObject([
                    FunctionObject::NAME => 'show',
                    FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
                    FunctionObject::DESCRIPTION => 'show user details.',
                    FunctionObject::NULLABLE_RESULT => false,
                    FunctionObject::RETURN_TYPE => '\\Illuminate\\Http\\Response',
                    FunctionObject::CONTENT => 'return new Response(\\App\\Models\\User::findOrFail($id));',
                    FunctionObject::PARAMETERS => [
                        new FunctionParameterObject([
                            FunctionParameterObject::NAME => 'id',
                            FunctionParameterObject::DESCRIPTION => 'User identifier to retrieve details',
                            FunctionParameterObject::TYPE => 'int',
                        ]),
                    ],
                ]),
                new FunctionObject([
                    FunctionObject::NAME => 'index',
                    FunctionObject::VISIBILITY_TYPE => ClassMemberVisibilityTypes::PUBLIC,
                    FunctionObject::DESCRIPTION => 'Get all users',
                    FunctionObject::NULLABLE_RESULT => false,
                    FunctionObject::RETURN_TYPE => '\\Illuminate\\Http\\Response',
                    FunctionObject::CONTENT => 'return new Response(\\App\\Models\\User::get());',
                    FunctionObject::PARAMETERS => [],
                ]),
            ],
        ]);

        $actual = $classGenerator->render($classObject, $this->classTemplate);
        $expected = <<<'EXPECTED'
<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;

/**
 * Controller to handle users-related API requests.
 *
 * @property-read User|null $user Authenticated user
 */
class UsersApiController extends AppApiController
{
    /**
     * How many constants we see in this class.
     */
    private const MAX_CONSTANTS_PER_CLASS = 1;

    /**
     * When request was started.
     *
     * @var Carbon|null
     */
    public $startedAt;

    /**
     * When request was ended.
     *
     * @var Carbon|null
     */
    public $finishedAt;

    /**
     * Show user details.
     *
     * @param integer $id User identifier to retrieve details
     *
     * @return Response
     */
    public function show(int $id): Response
    {
        return new Response(User::findOrFail($id));
    }

    /**
     * Get all users.
     *
     * @return Response
     */
    public function index(): Response
    {
        return new Response(User::get());
    }
}

EXPECTED;

        $this->assertEquals($expected, $actual);
    }
}
