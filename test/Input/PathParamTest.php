<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\PathParam;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;

use function array_reduce;
use function count;
use function dirname;
use function realpath;
use function strpos;

use const PHP_EOL;

class PathParamTest extends TestCase
{
    /** @var PathParam */
    private $param;

    public function setUp(): void
    {
        $this->param = new PathParam('test', PathParam::TYPE_FILE);
        $this->param->setDescription('Selected path');
    }

    public function testUsesValueRequiredOptionMode(): void
    {
        $this->assertSame(InputOption::VALUE_REQUIRED, $this->param->getOptionMode());
    }

    public function defaultValues(): iterable
    {
        $question = '<question>Selected path:</question>';
        $suffix   = PHP_EOL . ' > ';

        yield 'null' => [null, $question . $suffix];
        yield 'path' => ['path', $question . ' [<comment>path</comment>]' . $suffix];
    }

    /**
     * @dataProvider defaultValues
     */
    public function testCreatesStandardQuestionUsingDefaultValue(
        ?string $default,
        string $expectedQuestionText
    ): void {
        $this->param->setDefault($default);
        $question = $this->param->getQuestion();
        $this->assertEquals($expectedQuestionText, $question->getQuestion());
    }

    public function testQuestionContainsAnAutocompleter(): void
    {
        $this->param->setDefault('path');
        $question = $this->param->getQuestion();
        $this->assertIsCallable($question->getAutocompleterCallback());
    }

    public function testQuestionContainsAValidator(): void
    {
        $this->param->setDefault('path');
        $question = $this->param->getQuestion();
        $this->assertIsCallable($question->getValidator());
    }

    public function testValidatorReturnsNullIfValueIsNullAndParamIsNotRequired(): void
    {
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertNull($validator(null));
    }

    public function testValidatorRaisesExceptionIfValueIsNullAndRequired(): void
    {
        $this->param->setRequiredFlag(true);
        $validator = $this->param->getQuestion()->getValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid value: string expected');
        $validator(null);
    }

    public function testValidatorReturnsValueVerbatimIfDoesNotExistAndAllowedNotToExist(): void
    {
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertSame('path-that-does-not-exist', $validator('path-that-does-not-exist'));
    }

    public function testValidatorRaisesExceptionIfValueIsNonExistentPathAndMustExist(): void
    {
        $this->param->setPathMustExist(true);
        $validator = $this->param->getQuestion()->getValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path does not exist');
        $validator('path-that-does-not-exist');
    }

    public function testValidatorRaisesExceptionIfFileExistsButMustBeADirectory(): void
    {
        $param = new PathParam('test', PathParam::TYPE_DIR);
        $param->setPathMustExist(true);
        $validator = $param->getQuestion()->getValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path is not a valid directory');
        $validator(__FILE__);
    }

    public function testValidatorReturnsValueVerbatimIfFileExists(): void
    {
        $this->param->setPathMustExist(true);
        $validator = $this->param->getQuestion()->getValidator();

        $this->assertSame(__FILE__, $validator(__FILE__));
    }

    public function testValidatorReturnsValueVerbatimIfDirExists(): void
    {
        $param = new PathParam('test', PathParam::TYPE_DIR);
        $param->setPathMustExist(true);
        $validator = $param->getQuestion()->getValidator();

        $this->assertSame(__DIR__, $validator(__DIR__));
    }

    public function testAutocompleterReturnsFilesAndDirectoriesBasedOnProvidedInput(): void
    {
        $autocompleter = $this->param->getQuestion()->getAutocompleterCallback();
        $paths         = $autocompleter(__DIR__);

        $this->assertGreaterThan(0, count($paths));
        $actual = array_reduce($paths, function (bool $isValid, $path) {
            return $isValid && 0 === strpos($path, realpath(dirname(__DIR__)));
        }, true);

        $this->assertTrue($actual, 'One or more autocompletion paths were invalid');
    }

    public function testConstructorRaisesExceptionForInvalidTypeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type provided');
        new PathParam('test', 'not-a-valid-type');
    }

    public function testQuestionCreatedDoesNotIndicateMultiPromptByDefault(): void
    {
        $question = $this->param->getQuestion();
        self::assertStringNotContainsString(
            'Multiple entries allowed',
            $question->getQuestion()
        );
    }

    // phpcs:ignore Generic.Files.LineLength.TooLong
    public function testQuestionCreatedIncludesMultiPromptButNotRequiredPromptWhenValueAllowsMultipleButNotRequired(): void
    {
        $this->param->setAllowMultipleFlag(true);
        $this->param->setRequiredFlag(false);
        $question = $this->param->getQuestion();
        self::assertStringContainsString(
            'Multiple entries allowed',
            $question->getQuestion()
        );
        self::assertStringNotContainsString(
            'At least one entry is required. ',
            $question->getQuestion()
        );
    }

    public function testQuestionCreatedIncludesMultiPromptAndRequiredPromptWhenValueAllowsMultipleAndIsRequired(): void
    {
        $this->param->setAllowMultipleFlag(true);
        $this->param->setRequiredFlag(true);
        $question = $this->param->getQuestion();
        self::assertStringContainsString(
            'Multiple entries allowed',
            $question->getQuestion()
        );
        self::assertStringContainsString(
            'At least one entry is required. ',
            $question->getQuestion()
        );
    }

    public function testCallingSetAllowMultipleWithBooleanFalseAfterPreviouslyCallingItWithTrueRemovesOptionFlag(): void
    {
        $this->param->setAllowMultipleFlag(true);
        self::assertSame(InputOption::VALUE_IS_ARRAY, $this->param->getOptionMode() & InputOption::VALUE_IS_ARRAY);
        $this->param->setAllowMultipleFlag(false);
        self::assertSame(0, $this->param->getOptionMode() & InputOption::VALUE_IS_ARRAY);
    }
}
