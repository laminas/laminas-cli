<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Command;

use Laminas\Cli\Command\ParamAwareCommandTrait;
use Laminas\Cli\Input\BoolParam;
use Laminas\Cli\Input\ParamAwareInputInterface;
use LaminasTest\Cli\TestAsset\ParamAwareCommandStub;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParamAwareCommandTest extends TestCase
{
    /** @var Command */
    private $command;

    /** @var QuestionHelper|ObjectProphecy */
    private $questionHelper;

    public function setUp(): void
    {
        $this->questionHelper = $this->prophesize(QuestionHelper::class)->reveal();

        $helperSet = $this->prophesize(HelperSet::class);
        $helperSet->get('question')->willReturn($this->questionHelper);

        $this->command = new class ($helperSet->reveal()) extends ParamAwareCommandStub {
            use ParamAwareCommandTrait;

            /** @var array */
            public $options = [];

            /** HelperSet */
            private $helperSet;

            public function __construct(HelperSet $helperSet)
            {
                $this->helperSet = $helperSet;
            }

            /**
             * @param string|array|null $shortcut
             * @param null|mixed        $default Defaults to null.
             * @return $this
             */
            public function addOption(
                string $name,
                $shortcut = null,
                ?int $mode = null,
                string $description = '',
                $default = null
            ) {
                $this->options[$name] = [
                    'shortcut'    => $shortcut,
                    'mode'        => $mode,
                    'description' => $description,
                    'default'     => $default,
                ];
                return $this;
            }

            /**
             * @return HelperSet
             */
            public function getHelperSet()
            {
                return $this->helperSet;
            }
        };
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
        $this->assertSame($param->getShortcut(), $option['shortcut']);
        $this->assertSame($param->getOptionMode(), $option['mode']);
        $this->assertSame($param->getDescription(), $option['description']);
        $this->assertNull($option['default']); // Option default is always null!
    }

    public function testRunDecoratesInputInParameterAwareInputInstance(): void
    {
        $input  = $this->prophesize(InputInterface::class)->reveal();
        $output = $this->prophesize(OutputInterface::class)->reveal();
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
