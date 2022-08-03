<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\BoolParam;
use Laminas\Cli\Input\ChoiceParam;
use Laminas\Cli\Input\InputParamInterface;
use Laminas\Cli\Input\IntParam;
use Laminas\Cli\Input\ParamAwareInput;
use Laminas\Cli\Input\ParamAwareInputInterface;
use Laminas\Cli\Input\PathParam;
use Laminas\Cli\Input\StringParam;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\NullOutputFormatter;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function class_exists;
use function fopen;
use function fwrite;
use function preg_match;
use function rewind;
use function strpos;

use const PHP_EOL;
use const STDIN;

class ParamAwareInputTest extends TestCase
{
    /** @psalm-var class-string<ParamAwareInputInterface> */
    private string $class;

    /**
     * @var InputInterface|MockObject
     * @psalm-var InputInterface&MockObject
     */
    private $decoratedInput;

    /**
     * @var QuestionHelper|MockObject
     * @psalm-var QuestionHelper&MockObject
     */
    private $helper;

    /**
     * @var OutputInterface|MockObject
     * @psalm-var OutputInterface&MockObject
     */
    private $output;

    /** @var InputParamInterface[] */
    private array $params;

    public function setUp(): void
    {
        $this->class = ParamAwareInput::class;

        $this->decoratedInput = $this->createMock(InputInterface::class);
        $this->output         = $this->createMock(OutputInterface::class);
        $this->helper         = $this->createMock(QuestionHelper::class);

        $stringParam = new StringParam('name');
        $stringParam->setDescription('Your name');
        $stringParam->setRequiredFlag(true);

        $boolParam = new BoolParam('bool');
        $boolParam->setDescription('True or false');
        $boolParam->setRequiredFlag(true);

        $intParam = new IntParam('int');
        $intParam->setDescription('Integer');
        $intParam->setRequiredFlag(false);

        $choiceParam = new ChoiceParam('choices', ['a', 'b', 'c']);
        $choiceParam->setDescription('Choose one');
        $choiceParam->setDefault('a');

        $multiIntParamWithDefault = new IntParam('multi-int-with-default');
        $multiIntParamWithDefault->setDescription('Allowed integers');
        $multiIntParamWithDefault->setDefault([1, 2]);
        $multiIntParamWithDefault->setAllowMultipleFlag(true);

        $multiIntParamRequired = new IntParam('multi-int-required');
        $multiIntParamRequired->setDescription('Required integers');
        $multiIntParamRequired->setRequiredFlag(true);
        $multiIntParamRequired->setAllowMultipleFlag(true);

        $this->params = [
            'name'                   => $stringParam,
            'bool'                   => $boolParam,
            'int'                    => $intParam,
            'choices'                => $choiceParam,
            'multi-int-with-default' => $multiIntParamWithDefault,
            'multi-int-required'     => $multiIntParamRequired,
        ];
    }

    /**
     * @param string[] $inputs
     * @return resource
     */
    public function mockStream(array $inputs)
    {
        $stream = fopen('php://memory', 'r+', false);

        foreach ($inputs as $input) {
            fwrite($stream, $input . PHP_EOL);
        }

        rewind($stream);

        return $stream;
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:string,1:list<mixed>,2:mixed}>
     */
    public function proxyMethodsAndArguments(): iterable
    {
        // AbstractParamAwareInput methods
        yield 'getFirstArgument' => ['getFirstArgument', [], 'first'];

        $definition = $this->createMock(InputDefinition::class);
        yield 'bind' => ['bind', [$definition], null];

        yield 'validate' => ['validate', [], null];
        yield 'getArguments' => ['getArguments', [], ['first', 'second']];
        yield 'hasArgument' => ['hasArgument', ['argument'], true];
        yield 'getOptions' => ['getOptions', [], ['name', 'bool', 'choices']];
        yield 'isInteractive' => ['isInteractive', [], true];

        // Implementation-specific methods
        yield 'hasParameterOption' => ['hasParameterOption', [['some', 'values'], true], true];
        yield 'getParameterOption' => ['getParameterOption', [['some', 'values'], null, true], 'value'];
        yield 'getArgument' => ['getArgument', ['argument'], 'argument'];
        yield 'setArgument' => ['setArgument', ['argument', 'value'], null];
        yield 'getOption' => ['getOption', ['option'], 'option'];
        yield 'setOption' => ['setOption', ['option', 'value'], null];
        yield 'hasOption' => ['hasOption', ['option'], true];
        yield 'setInteractive' => ['setInteractive', [true], null];
    }

    /**
     * @dataProvider proxyMethodsAndArguments
     * @param mixed $expectedOutput
     */
    public function testProxiesToDecoratedInput(
        string $method,
        array $arguments,
        $expectedOutput
    ): void {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method($method)
            ->with(...$arguments)
            ->willReturn($expectedOutput);

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            $this->params
        );

        $this->assertSame($expectedOutput, $input->$method(...$arguments));
    }

    public function testSetStreamDoesNotProxyToDecoratedInputWhenNotStreamable(): void
    {
        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            $this->params
        );
        $this->assertNull($input->setStream(STDIN));
    }

    public function testGetStreamDoesNotProxyToDecoratedInputWhenNotStreamable(): void
    {
        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            $this->params
        );
        $this->assertNull($input->getStream());
    }

    public function testCanSetStreamOnDecoratedStreamableInput(): void
    {
        $decoratedInput = $this->createMock(StreamableInputInterface::class);
        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('setStream')
            ->with($this->identicalTo(STDIN))
            ->willReturn(null);

        $input = new $this->class(
            $decoratedInput,
            $this->output,
            $this->helper,
            $this->params
        );

        $this->assertNull($input->setStream(STDIN));
    }

    public function testCanRetrieveStreamFromDecoratedStreamableInput(): void
    {
        $decoratedInput = $this->createMock(StreamableInputInterface::class);
        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getStream')
            ->willReturn(STDIN);

        $input = new $this->class(
            $decoratedInput,
            $this->output,
            $this->helper,
            $this->params
        );

        $this->assertIsResource($input->getStream());
    }

    public function testGetParamRaisesExceptionIfParamDoesNotExist(): void
    {
        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            $this->params
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name');
        $input->getParam('does-not-exist');
    }

    public function testGetParamReturnsDefaultValueWhenInputIsNonInteractiveAndNoOptionPassed(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('choices'))
            ->willReturn(null);
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('isInteractive')
            ->willReturn(false);

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['choices' => $this->params['choices']]
        );

        $this->assertSame('a', $input->getParam('choices'));
    }

    public function testGetParamReturnsDefaultValueWhenInputIsNonInteractiveAndNoOptionPassedForArrayParam(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('multi-int-with-default'))
            ->willReturn(null);
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('isInteractive')
            ->willReturn(false);

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->assertSame([1, 2], $input->getParam('multi-int-with-default'));
    }

    /**
     * @see https://github.com/laminas/laminas-cli/issues/55
     */
    public function testGetParamReturnsOptionValueWhenInputOptionPassedForScalarParam(): void
    {
        $this->decoratedInput
            ->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('int'))
            // Values provided via command line are always strings.
            // This means we're also testing that normalizers are run first.
            ->willReturn('10');
        $this->decoratedInput
            ->expects($this->never())
            ->method('isInteractive');

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['int' => $this->params['int']]
        );

        $this->assertSame(10, $input->getParam('int'));
    }

    public function testGetParamReturnsOptionValueWhenInputOptionPassedForArrayParam(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('multi-int-with-default'))
            ->willReturn([10]);
        $this->decoratedInput
            ->expects($this->never())
            ->method('isInteractive');

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->assertSame([10], $input->getParam('multi-int-with-default'));
    }

    public function testGetParamRaisesExceptionWhenScalarProvidedForArrayParam(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('multi-int-with-default'))
            ->willReturn(1);
        $this->decoratedInput
            ->expects($this->never())
            ->method('isInteractive');

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects an array of values');
        $input->getParam('multi-int-with-default');
    }

    public function testGetParamRaisesExceptionWhenOptionValuePassedForArrayParamIsInvalid(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('multi-int-with-default'))
            ->willReturn(['string']);
        $this->decoratedInput
            ->expects($this->never())
            ->method('isInteractive');

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('integer expected');
        $input->getParam('multi-int-with-default');
    }

    public function testGetParamRaisesExceptionIfParameterIsRequiredButNotProvidedAndInputIsNoninteractive(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('name'))
            ->willReturn(null);
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('isInteractive')
            ->willReturn(false);

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['name' => $this->params['name']]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required value for --name parameter');
        $input->getParam('name');
    }

    public function testGetParamPromptsForValueWhenOptionIsNotProvidedAndInputIsInteractive(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('name'))
            ->willReturn(null);
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('isInteractive')
            ->willReturn(true);
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('setOption')
            ->with(
                $this->equalTo('name'),
                $this->equalTo('Laminas')
            );

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['name' => $this->params['name']]
        );

        // getQuestion returns a NEW instance each time, so we cannot test for
        // an identical question instance, only the type.
        $this->helper
            ->expects($this->atLeastOnce())
            ->method('ask')
            ->with(
                $this->equalTo($input),
                $this->equalTo($this->output),
                $this->isInstanceOf(Question::class)
            )
            ->willReturn('Laminas');

        $this->assertSame('Laminas', $input->getParam('name'));
    }

    public function testGetParamPromptsUntilEmptySubmissionWhenParamIsArrayAndInputIsInteractive(): void
    {
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('multi-int-with-default'))
            ->willReturn([]);
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('isInteractive')
            ->willReturn(true);
        $this->decoratedInput
            ->expects($this->atLeastOnce())
            ->method('setOption')
            ->with(
                $this->equalTo('multi-int-with-default'),
                $this->equalTo([10, 2, 7])
            );

        $input = new $this->class(
            $this->decoratedInput,
            $this->output,
            $this->helper,
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        // getQuestion returns a NEW instance each time, so we cannot test for
        // an identical question instance, only the type.
        $this->helper
            ->expects($this->exactly(4))
            ->method('ask')
            ->with(
                $this->equalTo($input),
                $this->equalTo($this->output),
                $this->isInstanceOf(Question::class)
            )
            ->will($this->onConsecutiveCalls(10, 2, 7, null));

        $this->assertSame([10, 2, 7], $input->getParam('multi-int-with-default'));
    }

    public function testGetParamPromptsForValuesUntilAtLeastOneIsProvidedWhenRequired(): void
    {
        $decoratedInput = $this->createMock(StreamableInputInterface::class);
        // This sets us up to enter the following lines:
        // - An empty line (rejected by the IntParam validator)
        // - A line with the string "10" on it (accepted by the IntParam
        //   validator, and cast to integer by its normalizer)
        // - A line with the string 'hey' on it (marked invalid by the IntParam
        //   validator)
        // - A line with the string "1" on it (accepted by the IntParam
        //   validator, and cast to integer by its normalizer)
        // - An empty line (accepted by the modified validator, since we now
        //   have a value from the previous line)
        $stream = $this->mockStream([
            '',
            '10',
            'hey',
            '1',
            '',
        ]);
        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getStream')
            ->willReturn($stream);

        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('multi-int-required'))
            ->willReturn([]);

        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('isInteractive')
            ->willReturn(true);

        $decoratedInput
            ->expects($this->once())
            ->method('setOption')
            ->with(
                $this->equalTo('multi-int-required'),
                $this->equalTo([10, 1])
            );

        $this->output
            ->expects($this->exactly(5))
            ->method('write')
            ->with($this->stringContains('<question>'));

        $this->output
            ->expects($this->exactly(2))
            ->method('writeln')
            ->with($this->stringContains('<error>'));

        $helper = new QuestionHelper();
        $input  = new $this->class(
            $decoratedInput,
            $this->output,
            $helper,
            ['multi-int-required' => $this->params['multi-int-required']]
        );

        $this->assertSame([10, 1], $input->getParam('multi-int-required'));
    }

    /**
     * @psalm-return iterable<non-empty-string,array{0:class-string<InputParamInterface>,1?:list<mixed>}>
     */
    public function paramTypesToTestAgainstFalseRequiredFlag(): iterable
    {
        yield 'IntParam'    => [IntParam::class];
        yield 'PathParam'   => [PathParam::class, [PathParam::TYPE_DIR]];
        yield 'StringParam' => [StringParam::class];
    }

    /**
     * @dataProvider paramTypesToTestAgainstFalseRequiredFlag
     * @psalm-param class-string<InputParamInterface> $class
     */
    public function testGetParamAllowsEmptyValuesForParamsWithValidationIfParamIsNotRequired(
        string $class,
        array $additionalArgs = []
    ): void {
        $stream         = $this->mockStream(['']);
        $decoratedInput = $this->createMock(StreamableInputInterface::class);
        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getStream')
            ->willReturn($stream);

        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('getOption')
            ->with($this->equalTo('non-required'))
            ->willReturn(null);

        $decoratedInput
            ->expects($this->atLeastOnce())
            ->method('isInteractive')
            ->willReturn(true);

        $decoratedInput
            ->expects($this->once())
            ->method('setOption')
            ->with($this->equalTo('non-required'), $this->isNull());

        if (class_exists(NullOutputFormatter::class)) {
            $formatter = new NullOutputFormatter();

            $this->output
                ->expects($this->any())
                ->method('getFormatter')
                ->willReturn($formatter);
        }

        $this->output
            ->expects($this->any())
            ->method('write')
            ->with($this->callback(static fn(string $message): bool => preg_match('/^\s*$/', $message)
                || false !== strpos($message, '<question>')));

        $helper = new QuestionHelper();
        $input  = new $this->class(
            $decoratedInput,
            $this->output,
            $helper,
            ['non-required' => new $class('non-required', ...$additionalArgs)]
        );

        $this->assertNull($input->getParam('non-required'));
    }
}
