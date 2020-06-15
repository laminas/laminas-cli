<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\StringParam;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;

use const PHP_EOL;

class StringParamTest extends TestCase
{
    /** @var StringParam */
    private $param;

    public function setUp(): void
    {
        $this->param = new StringParam('test');
        $this->param->setDescription('A string');
    }

    public function testUsesValueRequiredOptionMode(): void
    {
        $this->assertSame(InputOption::VALUE_REQUIRED, $this->param->getOptionMode());
    }

    public function defaultValues(): iterable
    {
        $question = '<question>A string:</question>';
        $suffix   = PHP_EOL . ' > ';

        yield 'null' => [null, $question . $suffix];
        yield 'string' => ['string', $question . ' [<comment>string</comment>]' . $suffix];
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

    public function testQuestionContainsAValidator(): void
    {
        $validator = $this->param->getQuestion()->getValidator();
        $this->assertIsCallable($validator);
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

    public function testValidatorReturnsValueVerbatimIfNoPatternProvided(): void
    {
        $this->param->setRequiredFlag(true);
        $validator = $this->param->getQuestion()->getValidator();

        $this->assertSame('a string', $validator('a string'));
    }

    public function testValidatorRaisesExceptionIfValueDoesNotMatchProvidedPattern(): void
    {
        $this->param->setRequiredFlag(true);
        $this->param->setPattern('/^[A-Z][a-zA-Z0-9_]+$/');
        $validator = $this->param->getQuestion()->getValidator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid value: does not match pattern');
        $validator('this does not match the pattern');
    }

    public function testValidatorReturnsValueVerbatimIfMatchesPatternProvided(): void
    {
        $this->param->setRequiredFlag(true);
        $this->param->setPattern('/^[A-Z][a-zA-Z0-9_]+$/');
        $validator = $this->param->getQuestion()->getValidator();

        $this->assertSame('AClassName', $validator('AClassName'));
    }

    public function testSetPatternRaisesExceptionIfPatternIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid PCRE pattern');

        $this->param->setPattern('This is#^ NOT** a! pattern,');
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
