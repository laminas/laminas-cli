<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\BoolParam;
use Laminas\Cli\Input\ChoiceParam;
use Laminas\Cli\Input\InputParamInterface;
use Laminas\Cli\Input\IntParam;
use Laminas\Cli\Input\NonHintedParamAwareInput;
use Laminas\Cli\Input\PathParam;
use Laminas\Cli\Input\StringParam;
use Laminas\Cli\Input\TypeHintedParamAwareInput;
use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function fopen;
use function fwrite;
use function rewind;
use function str_replace;
use function strstr;

use const PHP_EOL;
use const STDIN;

class ParamAwareInputTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var string
     * @psalm-var class-string<\Laminas\Cli\Input\ParamAwareInputInterface>
     */
    private $class;

    /**
     * @var InputInterface|ObjectProphecy 
     * @psalm-var InputInterface&ObjectProphecy
     */
    private $decoratedInput;

    /**
     * @var QuestionHelper|ObjectProphecy 
     * @psalm-var QuestionHelper&ObjectProphecy
     */
    private $helper;

    /**
     * @var OutputInterface|ObjectProphecy 
     * @psalm-var OutputInterface&ObjectProphecy
     */
    private $output;

    /** @var InputParamInterface[] */
    private $params;

    public function setUp(): void
    {
        /** @psalm-suppress DeprecatedClass */
        $consoleVersion = strstr(Versions::getVersion('symfony/console'), '@', true) ?: '';
        $this->class    = str_replace('v', '', $consoleVersion) >= '5.0.0'
            ? TypeHintedParamAwareInput::class
            : NonHintedParamAwareInput::class;

        /** @psalm-var InputInterface&ObjectProphecy $decoratedInput */
        $decoratedInput = $this->prophesize(InputInterface::class);
        $this->decoratedInput = $decoratedInput;

        /** @psalm-var OutputInterface&ObjectProphecy $output */
        $output = $this->prophesize(OutputInterface::class);
        $this->output = $output;
        
        /** @psalm-var QuestionHelper&ObjectProphecy $helper */
        $helper = $this->prophesize(QuestionHelper::class);
        $this->helper = $helper;

        $this->params         = [
            'name'                   => (new StringParam('name'))
                ->setDescription('Your name')
                ->setRequiredFlag(true),
            'bool'                   => (new BoolParam('bool'))
                ->setDescription('True or false')
                ->setRequiredFlag(true),
            'choices'                => (new ChoiceParam('choices', ['a', 'b', 'c']))
                ->setDescription('Choose one')
                ->setDefault('a'),
            'multi-int-with-default' => (new IntParam('multi-int-with-default'))
                ->setDescription('Allowed integers')
                ->setDefault([1, 2])
                ->setAllowMultipleFlag(true),
            'multi-int-required'     => (new IntParam('multi-int-required'))
                ->setDescription('Required integers')
                ->setRequiredFlag(true)
                ->setAllowMultipleFlag(true),
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

    public function proxyMethodsAndArguments(): iterable
    {
        // AbstractParamAwareInput methods
        yield 'getFirstArgument' => ['getFirstArgument', [], 'first'];

        $definition = $this->prophesize(InputDefinition::class)->reveal();
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
        /** @psalm-suppress all */
        $this->decoratedInput
            ->$method(...$arguments)
            ->willReturn($expectedOutput)
            ->shouldBeCalled();
        
        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            $this->params
        );
        
        $this->assertSame($expectedOutput, $input->$method(...$arguments));
    }

    public function testSetStreamDoesNotProxyToDecoratedInputWhenNotStreamable(): void
    {
        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            $this->params
        );
        $this->assertNull($input->setStream(STDIN));
    }

    public function testGetStreamDoesNotProxyToDecoratedInputWhenNotStreamable(): void
    {
        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            $this->params
        );
        $this->assertNull($input->getStream());
    }

    public function testCanSetStreamOnDecoratedStreamableInput(): void
    {
        $decoratedInput = $this->prophesize(StreamableInputInterface::class);
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->setStream(STDIN)->willReturn(null)->shouldBeCalled();

        $input = new $this->class(
            $decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            $this->params
        );

        $this->assertNull($input->setStream(STDIN));
    }

    public function testCanRetrieveStreamFromDecoratedStreamableInput(): void
    {
        $decoratedInput = $this->prophesize(StreamableInputInterface::class);
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->getStream()->willReturn(STDIN)->shouldBeCalled();

        $input = new $this->class(
            $decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            $this->params
        );

        $this->assertIsResource($input->getStream());
    }

    public function testGetParamRaisesExceptionIfParamDoesNotExist(): void
    {
        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            $this->params
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter name');
        $input->getParam('does-not-exist');
    }

    public function testConstructorRaisesErrorIfAnyParamIsOfInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(InputParamInterface::class);

        new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['name' => new stdClass()]
        );
    }

    public function testGetParamReturnsDefaultValueWhenInputIsNonInteractiveAndNoOptionPassed(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('choices')->willReturn(null)->shouldBeCalled();
        /** @psalm-suppress all */
        $this->decoratedInput->isInteractive()->willReturn(false)->shouldBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['choices' => $this->params['choices']]
        );

        $this->assertSame('a', $input->getParam('choices'));
    }

    public function testGetParamReturnsDefaultValueWhenInputIsNonInteractiveAndNoOptionPassedForArrayParam(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('multi-int-with-default')->willReturn(null)->shouldBeCalled();
        /** @psalm-suppress all */
        $this->decoratedInput->isInteractive()->willReturn(false)->shouldBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->assertSame([1, 2], $input->getParam('multi-int-with-default'));
    }

    public function testGetParamReturnsOptionValueWhenInputOptionPassedForArrayParam(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('multi-int-with-default')->willReturn([10])->shouldBeCalled();
        /** @psalm-suppress MixedMethodCall */
        $this->decoratedInput->isInteractive()->shouldNotBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->assertSame([10], $input->getParam('multi-int-with-default'));
    }

    public function testGetParamRaisesExceptionWhenScalarProvidedForArrayParam(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('multi-int-with-default')->willReturn(1)->shouldBeCalled();
        /** @psalm-suppress MixedMethodCall */
        $this->decoratedInput->isInteractive()->shouldNotBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects an array of values');
        $input->getParam('multi-int-with-default');
    }

    public function testGetParamRaisesExceptionWhenOptionValuePassedForArrayParamIsInvalid(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('multi-int-with-default')->willReturn(['string'])->shouldBeCalled();
        /** @psalm-suppress MixedMethodCall */
        $this->decoratedInput->isInteractive()->shouldNotBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('integer expected');
        $input->getParam('multi-int-with-default');
    }

    public function testGetParamRaisesExceptionIfParameterIsRequiredButNotProvidedAndInputIsNoninteractive(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('name')->willReturn(null)->shouldBeCalled();
        /** @psalm-suppress all */
        $this->decoratedInput->isInteractive()->willReturn(false)->shouldBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['name' => $this->params['name']]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required value for --name parameter');
        $input->getParam('name');
    }

    public function testGetParamPromptsForValueWhenOptionIsNotProvidedAndInputIsInteractive(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('name')->willReturn(null)->shouldBeCalled();
        /** @psalm-suppress all */
        $this->decoratedInput->isInteractive()->willReturn(true)->shouldBeCalled();
        $this->decoratedInput->setOption('name', 'Laminas')->shouldBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['name' => $this->params['name']]
        );

        // getQuestion returns a NEW instance each time, so we cannot test for
        // an identical question instance, only the type.
        /** @psalm-suppress all */
        $this->helper
            ->ask(
                $input,
                Argument::that([$this->output, 'reveal']),
                Argument::type(Question::class)
            )
            ->willReturn('Laminas')
            ->shouldBeCalled();

        $this->assertSame('Laminas', $input->getParam('name'));
    }

    public function testGetParamPromptsUntilEmptySubmissionWhenParamIsArrayAndInputIsInteractive(): void
    {
        /** @psalm-suppress all */
        $this->decoratedInput->getOption('multi-int-with-default')->willReturn([])->shouldBeCalled();
        /** @psalm-suppress all */
        $this->decoratedInput->isInteractive()->willReturn(true)->shouldBeCalled();
        /** @psalm-suppress all */
        $this->decoratedInput->setOption('multi-int-with-default', [10, 2, 7])->shouldBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['multi-int-with-default' => $this->params['multi-int-with-default']]
        );

        // getQuestion returns a NEW instance each time, so we cannot test for
        // an identical question instance, only the type.
        /** @psalm-suppress all */
        $this->helper
            ->ask(
                $input,
                Argument::that([$this->output, 'reveal']),
                Argument::type(Question::class)
            )
            ->willReturn(10, 2, 7, null)
            ->shouldBeCalledTimes(4);

        $this->assertSame([10, 2, 7], $input->getParam('multi-int-with-default'));
    }

    public function testGetParamPromptsForValuesUntilAtLeastOneIsProvidedWhenRequired(): void
    {
        $decoratedInput = $this->prophesize(StreamableInputInterface::class);
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
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->getStream()->willReturn($this->mockStream([
            '',
            '10',
            'hey',
            '1',
            '',
        ]));
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->getOption('multi-int-required')->willReturn([])->shouldBeCalled();
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->isInteractive()->willReturn(true)->shouldBeCalled();
        $decoratedInput->setOption('multi-int-required', [10, 1])->shouldBeCalled();

        /** @psalm-suppress ImplicitToStringCast */
        $this->output->write(Argument::containingString('<question>'))->shouldBeCalled();
        /** @psalm-suppress ImplicitToStringCast */
        $this->output->writeln(Argument::containingString('<error>'))->shouldBeCalledTimes(2);

        $helper = new QuestionHelper();
        $input  = new $this->class(
            $decoratedInput->reveal(),
            $this->output->reveal(),
            $helper,
            ['multi-int-required' => $this->params['multi-int-required']]
        );

        $this->assertSame([10, 1], $input->getParam('multi-int-required'));
    }

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
        $decoratedInput = $this->prophesize(StreamableInputInterface::class);
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->getStream()->willReturn($this->mockStream(['']));
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->getOption('non-required')->willReturn(null)->shouldBeCalled();
        /** @psalm-suppress TooManyArguments */
        $decoratedInput->isInteractive()->willReturn(true)->shouldBeCalled();
        $decoratedInput->setOption('non-required', null)->shouldBeCalled();

        /** @psalm-suppress ImplicitToStringCast */
        $this->output->write(Argument::containingString('<question>'))->shouldBeCalled();

        $helper = new QuestionHelper();
        $input  = new $this->class(
            $decoratedInput->reveal(),
            $this->output->reveal(),
            $helper,
            ['non-required' => new $class('non-required', ...$additionalArgs)]
        );

        $this->assertNull($input->getParam('non-required'));
    }
}
