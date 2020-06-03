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
use Laminas\Cli\Input\NonHintedParamAwareInput;
use Laminas\Cli\Input\StringParam;
use Laminas\Cli\Input\TypeHintedParamAwareInput;
use PackageVersions\Versions;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function str_replace;
use function strstr;

use const STDIN;

class ParamAwareInputTest extends TestCase
{
    /** @var string */
    private $class;

    /** @var InputInterface|ObjectProphecy */
    private $decoratedInput;

    /** @var QuestionHelper|ObjectProphecy */
    private $helper;

    /** @var OutputInterface|ObjectProphecy */
    private $output;

    /** @var InputParamInterface[] */
    private $params;

    public function setUp(): void
    {
        $consoleVersion = strstr(Versions::getVersion('symfony/console'), '@', true);
        $this->class    = str_replace('v', '', $consoleVersion) >= '5.0.0'
            ? TypeHintedParamAwareInput::class
            : NonHintedParamAwareInput::class;

        $this->decoratedInput = $this->prophesize(InputInterface::class);
        $this->output         = $this->prophesize(OutputInterface::class);
        $this->helper         = $this->prophesize(QuestionHelper::class);
        $this->params         = [
            'name'    => (new StringParam('name'))
                ->setDescription('Your name')
                ->setRequiredFlag(true),
            'bool'    => (new BoolParam('bool'))
                ->setDescription('True or false')
                ->setRequiredFlag(true),
            'choices' => (new ChoiceParam('choices', ['a', 'b', 'c']))
                ->setDescription('Choose one')
                ->setDefault('a'),
        ];
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

    public function testGetParamRaisesExceptionIfIdentifiedParameterIsOfInvalidType(): void
    {
        $this->decoratedInput->getOption('name')->willReturn(null)->shouldBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['name' => new stdClass()]
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameter type');

        $input->getParam('name');
    }

    public function testGetParamReturnsDefaultValueWhenInputIsNonInteractiveAndNoOptionPassed(): void
    {
        $this->decoratedInput->getOption('choices')->willReturn(null)->shouldBeCalled();
        $this->decoratedInput->isInteractive()->willReturn(false)->shouldBeCalled();

        $input = new $this->class(
            $this->decoratedInput->reveal(),
            $this->output->reveal(),
            $this->helper->reveal(),
            ['choices' => $this->params['choices']]
        );

        $this->assertSame('a', $input->getParam('choices'));
    }

    public function testGetParamRaisesExceptionIfParameterIsRequiredButNotProvidedAndInputIsNoninteractive(): void
    {
        $this->decoratedInput->getOption('name')->willReturn(null)->shouldBeCalled();
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
        $this->decoratedInput->getOption('name')->willReturn(null)->shouldBeCalled();
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
}
