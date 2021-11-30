<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Command;

use Laminas\Cli\Input\BoolParam;
use Laminas\Cli\Input\ParamAwareInputInterface;
use LaminasTest\Cli\TestAsset\ParamAwareCommandStub74;
use LaminasTest\Cli\TestAsset\ParamAwareCommandStub80;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function version_compare;

use const PHP_VERSION;

class ParamAwareCommandTest extends TestCase
{
    /** @var ParamAwareCommandStub74|ParamAwareCommandStub80 */
    private $command;

    /**
     * @var QuestionHelper|MockObject
     * @psalm-var QuestionHelper&MockObject
     */
    private $questionHelper;

    public function setUp(): void
    {
        $this->questionHelper = $this->createMock(QuestionHelper::class);

        /** @psalm-var HelperSet&MockObject $helperSet */
        $helperSet = $this->createMock(HelperSet::class);
        $helperSet
            ->expects($this->any())
            ->method('get')
            ->with(
                $this->equalTo('question')
            )
            ->willReturn($this->questionHelper);

        $stubClass     = version_compare(PHP_VERSION, '8.0.0', '>=')
            ? ParamAwareCommandStub80::class
            : ParamAwareCommandStub74::class;
        $this->command = new $stubClass($helperSet);
    }

    public function testAddParamProxiesToAddOption(): void
    {
        $param = (new BoolParam('test'))
            ->setDescription('Yes or no')
            ->setDefault(false)
            ->setShortcut('t')
            ->setRequiredFlag(true);

        $this->assertSame($this->command, $this->command->addParam($param));

        $this->assertArrayHasKey('test', $this->command->options);

        $option = $this->command->options['test'];
        $this->assertIsArray($option);
        $this->assertSame($param->getShortcut(), $option['shortcut']);
        $this->assertSame($param->getOptionMode(), $option['mode']);
        $this->assertSame($param->getDescription(), $option['description']);
        $this->assertNull($option['default']); // Option default is always null!
    }

    public function testRunDecoratesInputInParameterAwareInputInstance(): void
    {
        /** @psalm-var InputInterface&MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input
            ->method('hasArgument')
            ->willReturn(false);

        /** @psalm-var OutputInterface&MockObject $output */
        $output = $this->createMock(OutputInterface::class);
        $param  = (new BoolParam('test'))
            ->setDescription('Yes or no')
            ->setDefault(false)
            ->setShortcut('t')
            ->setRequiredFlag(true);

        $this->command->addParam($param);
        $this->assertSame(0, $this->command->run($input, $output));

        $this->assertInstanceOf(ParamAwareInputInterface::class, $this->command->input);
        $this->assertSame($output, $this->command->output);
    }
}
