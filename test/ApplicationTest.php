<?php

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ApplicationFactory;
use Laminas\Cli\ApplicationProvisioner;
use LaminasTest\Cli\TestAsset\Chained1Command;
use LaminasTest\Cli\TestAsset\Chained2Command;
use LaminasTest\Cli\TestAsset\Chained3Command;
use LaminasTest\Cli\TestAsset\ExampleCommand;
use LaminasTest\Cli\TestAsset\ExampleCommandWithDependencies;
use LaminasTest\Cli\TestAsset\ExampleCommandWithDependenciesFactory;
use LaminasTest\Cli\TestAsset\ExampleDependency;
use LaminasTest\Cli\TestAsset\ExampleDependencyFactory;
use LaminasTest\Cli\TestAsset\InputMapper\CustomInputMapper;
use LaminasTest\Cli\TestAsset\ParamCommand;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

use function array_filter;
use function current;

/** @psalm-suppress PropertyNotSetInConstructor */
class ApplicationTest extends TestCase
{
    public static function getValidConfiguration(): array
    {
        return [
            'commands' => [
                'example:command-name' => ExampleCommand::class,
                'example:chained-1'    => Chained1Command::class,
                'example:chained-2'    => Chained2Command::class,
                'example:chained-3'    => Chained3Command::class,
            ],
            'chains'   => [
                ExampleCommand::class  => [
                    Chained1Command::class => ['arg' => 'arg1', '--opt' => '--opt1'],
                    Chained3Command::class => ['arg' => 'arg3', '--opt' => '--opt3'],
                ],
                Chained1Command::class => [
                    Chained2Command::class => ['arg1' => 'arg2', '--opt1' => '--opt2'],
                ],
            ],
        ];
    }

    /** @psalm-param int[] $exitCodes */
    private function getApplication(array $exitCodes = []): Application
    {
        /** @psalm-var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject */
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnMap([
            [ExampleCommand::class, true],
            [Chained1Command::class, true],
            [Chained2Command::class, true],
            [Chained3Command::class, true],
        ]);
        $container->method('get')->willReturnMap([
            ['config', ['laminas-cli' => $this->getValidConfiguration()]],
            [ExampleCommand::class, new ExampleCommand($exitCodes[0] ?? 0)],
            [Chained1Command::class, new Chained1Command($exitCodes[1] ?? 0)],
            [Chained2Command::class, new Chained2Command($exitCodes[2] ?? 0)],
            [Chained3Command::class, new Chained3Command($exitCodes[3] ?? 0)],
        ]);

        return $this->createApplicationInstance($container);
    }

    /**
     * @see Command
     *
     * @psalm-return iterable<non-empty-string,array{
     *     0:list<string>,
     *     1:list<string>,
     *     2:list<class-string>,
     *     3?:list<int>
     * }>
     */
    public function chainAnswer(): iterable
    {
        yield 'execute whole chain' => [
            ['Y', 'Y', 'Y'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                Chained2Command::class . ': arg=foo, opt=bar',
                Chained3Command::class . ': arg=foo, opt=bar',
            ],
            [],
        ];

        yield 'skip first chained' => [
            ['s', 'Y'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                'Skipping example:chained-1',
                Chained3Command::class . ': arg=foo, opt=bar',
            ],
            [
                Chained1Command::class,
                Chained2Command::class,
            ],
        ];

        yield 'skip second chained' => [
            ['Y', 's', 'Y'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                'Skipping example:chained-2',
                Chained3Command::class . ': arg=foo, opt=bar',
            ],
            [
                Chained2Command::class,
            ],
        ];

        yield 'skip third chained' => [
            ['Y', 'Y', 's'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                Chained2Command::class . ': arg=foo, opt=bar',
                'Skipping example:chained-3',
            ],
            [
                Chained3Command::class,
            ],
        ];

        yield 'skip second and third chained' => [
            ['Y', 's', 's'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                'Skipping example:chained-2',
                'Skipping example:chained-3',
            ],
            [
                Chained2Command::class,
                Chained3Command::class,
            ],
        ];

        yield 'skip first and third chained' => [
            ['s', 's'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                'Skipping example:chained-1',
                'Skipping example:chained-3',
            ],
            [
                Chained1Command::class,
                Chained2Command::class,
                Chained3Command::class,
            ],
        ];

        yield 'break on first chained' => [
            ['n'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                'Break on example:chained-1',
            ],
            [
                Chained1Command::class,
                Chained2Command::class,
                Chained3Command::class,
            ],
        ];

        yield 'break on second chained' => [
            ['Y', 'n', 'Y'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                'Break on example:chained-2',
                Chained3Command::class . ': arg=foo, opt=bar',
            ],
            [
                Chained2Command::class,
            ],
        ];

        yield 'break on second chained, skip third' => [
            ['Y', 'n', 's'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                'Break on example:chained-2',
                'Skipping example:chained-3',
            ],
            [
                Chained2Command::class,
                Chained3Command::class,
            ],
        ];

        // exit codes
        yield 'exit on first command' => [
            [],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
            ],
            [
                Chained1Command::class,
                Chained2Command::class,
                Chained3Command::class,
            ],
            [13],
        ];

        yield 'exit on first chained command' => [
            ['Y'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
            ],
            [
                Chained2Command::class,
                Chained3Command::class,
            ],
            [0, 17],
        ];

        yield 'exit on second chained command' => [
            ['Y', 'Y'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                Chained2Command::class . ': arg=foo, opt=bar',
            ],
            [
                Chained3Command::class,
            ],
            [0, 0, 3],
        ];
    }

    /**
     * @dataProvider chainAnswer
     * @param string[] $answers
     * @param string[] $contains
     * @param string[] $doesNotContain
     * @param int[]    $exitCodes
     */
    public function testChainCommand(
        array $answers,
        array $contains,
        array $doesNotContain,
        array $exitCodes = []
    ): void {
        $application = $this->getApplication($exitCodes);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs($answers);
        $statusCode = $applicationTester->run(
            [
                'command' => 'example:command-name',
                'arg'     => 'foo',
                '--opt'   => 'bar',
            ],
            [
                'interactive' => true,
            ]
        );

        self::assertSame(current(array_filter($exitCodes)) ?: 0, $statusCode);
        $display = $applicationTester->getDisplay();
        foreach ($contains as $str) {
            self::assertStringContainsString($str, $display, 'Output does not contain ' . $str . "\n" . $display);
        }
        foreach ($doesNotContain as $str) {
            self::assertStringNotContainsString($str, $display, 'Output contains ' . $str . "\n" . $display);
        }
    }

    public function testPassCustomParams(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnMap([
            [ExampleCommand::class, true],
            [Chained1Command::class, true],
        ]);
        $container->method('get')->willReturnMap([
            [
                'config',
                [
                    'laminas-cli' => [
                        'commands' => [
                            'example:command-name' => ExampleCommand::class,
                            'example:chained-1'    => Chained1Command::class,
                        ],
                        'chains'   => [
                            ExampleCommand::class => [
                                Chained1Command::class => [
                                    ['arg1' => 'hey'],
                                    ['--opt1' => 'hello'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [ExampleCommand::class, new ExampleCommand()],
            [Chained1Command::class, new Chained1Command()],
        ]);

        $application = $this->createApplicationInstance($container);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs(['Y']);
        $statusCode = $applicationTester->run(
            [
                'command' => 'example:command-name',
                'arg'     => 'foo',
                '--opt'   => 'bar',
            ],
            [
                'interactive' => true,
            ]
        );

        self::assertSame(0, $statusCode);

        $contains = [
            ExampleCommand::class . ': arg=foo, opt=bar',
            Chained1Command::class . ': arg=hey, opt=hello',
        ];

        $display = $applicationTester->getDisplay();
        foreach ($contains as $str) {
            self::assertStringContainsString($str, $display, 'Output does not contain ' . $str . "\n" . $display);
        }
    }

    public function testCustomInputMapper(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnMap([
            [ExampleCommand::class, true],
            [Chained1Command::class, true],
        ]);
        $container->method('get')->willReturnMap([
            [
                'config',
                [
                    'laminas-cli' => [
                        'commands' => [
                            'example:command-name' => ExampleCommand::class,
                            'example:chained-1'    => Chained1Command::class,
                        ],
                        'chains'   => [
                            ExampleCommand::class => [
                                Chained1Command::class => CustomInputMapper::class,
                            ],
                        ],
                    ],
                ],
            ],
            [ExampleCommand::class, new ExampleCommand()],
            [Chained1Command::class, new Chained1Command()],
        ]);

        $application = $this->createApplicationInstance($container);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs(['Y']);
        $statusCode = $applicationTester->run(
            [
                'command' => 'example:command-name',
                'arg'     => 'foo',
                '--opt'   => 'bar',
            ],
            [
                'interactive' => true,
            ]
        );

        self::assertSame(0, $statusCode);

        $contains = [
            ExampleCommand::class . ': arg=foo, opt=bar',
            Chained1Command::class . ': arg=Foo Bar, opt=my-value',
        ];

        $display = $applicationTester->getDisplay();
        foreach ($contains as $str) {
            self::assertStringContainsString($str, $display, 'Output does not contain ' . $str . "\n" . $display);
        }
    }

    public function testList(): void
    {
        $application = $this->getApplication();

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs(['Y']);
        $statusCode = $applicationTester->run(['command' => 'list']);

        $display = $applicationTester->getDisplay();

        $contains = ' example' . "\n"
            . '  example:chained-1     Description of example:chained-1' . "\n"
            . '  example:chained-2     Description of example:chained-2' . "\n"
            . '  example:chained-3     Description of example:chained-3' . "\n"
            . '  example:command-name  Description of example:command-name' . "\n";

        self::assertSame(0, $statusCode);
        self::assertStringContainsString(
            $contains,
            $display,
            'Output does not contain: ' . "\n" . $contains . "\n" . '---' . "\n" . $display
        );
    }

    public function testParamInput(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnMap([
            [ParamCommand::class, true],
            [Chained1Command::class, true],
        ]);
        $container->method('get')->willReturnMap([
            [
                'config',
                [
                    'laminas-cli' => [
                        'commands' => [
                            'example:param'     => ParamCommand::class,
                            'example:chained-1' => Chained1Command::class,
                        ],
                        'chains'   => [
                            ParamCommand::class => [
                                Chained1Command::class => ['--int-param' => '--opt1'],
                            ],
                        ],
                    ],
                ],
            ],
            [ParamCommand::class, new ParamCommand()],
            [Chained1Command::class, new Chained1Command()],
        ]);

        $application = $this->createApplicationInstance($container);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs(['', '13', '-4', '5', 'Y']);
        $statusCode = $applicationTester->run(
            ['command' => 'example:param'],
            ['interactive' => true]
        );

        self::assertSame(0, $statusCode);

        $contains = [
            'Invalid value: integer expected, null given',
            'Invalid value 13; maximum value is 10',
            'Invalid value -4; minimum value is 1',
            'Int param value: 5',
            Chained1Command::class . ': arg=, opt=5',
        ];

        $display = $applicationTester->getDisplay();
        foreach ($contains as $str) {
            self::assertStringContainsString($str, $display, 'Output does not contain ' . $str . "\n" . $display);
        }
    }

    public function testParamInputNonInteractiveMissingParameter(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->atLeast(2))
            ->method('has')
            ->willReturnMap([
                ['Laminas\Cli\SymfonyEventDispatcher', false],
                [ParamCommand::class, true],
            ]);
        $container->method('get')->willReturnMap([
            [
                'config',
                [
                    'laminas-cli' => [
                        'commands' => [
                            'example:param' => ParamCommand::class,
                        ],
                    ],
                ],
            ],
            [ParamCommand::class, new ParamCommand()],
        ]);

        $application = $this->createApplicationInstance($container);

        $applicationTester = new ApplicationTester($application);
        $statusCode        = $applicationTester->run(
            ['command' => 'example:param'],
            ['interactive' => false]
        );

        self::assertSame(1, $statusCode);

        $contains = [
            'Missing required value for --int-param parameter',
        ];

        $display = $applicationTester->getDisplay();
        foreach ($contains as $str) {
            self::assertStringContainsString($str, $display, 'Output does not contain ' . $str . "\n" . $display);
        }
    }

    /**
     * @see https://github.com/laminas/laminas-cli/pull/28
     * @see https://github.com/laminas/laminas-cli/pull/29
     */
    public function testListIncludesCommandWithDependencies(): void
    {
        $config = [
            'laminas-cli' => [
                'commands' => [
                    'example:dep' => ExampleCommandWithDependencies::class,
                ],
            ],
        ];

        /** @psalm-var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->atLeast(2))
            ->method('has')
            ->willReturnMap([
                ['Laminas\Cli\SymfonyEventDispatcher', false],
                [ExampleCommandWithDependencies::class, true],
            ]);

        $container
            ->method('get')
            ->will($this->returnCallback(function (string $service) use ($config, $container) {
                switch ($service) {
                    case 'config':
                        return $config;
                    case ExampleDependency::class:
                        $factory = new ExampleDependencyFactory();
                        return $factory($container);
                    case ExampleCommandWithDependencies::class:
                        $factory = new ExampleCommandWithDependenciesFactory();
                        return $factory($container);
                    default:
                        return null;
                }
            }));

        $application = $this->createApplicationInstance($container);

        $applicationTester = new ApplicationTester($application);
        $statusCode        = $applicationTester->run(['command' => 'list']);

        $display = $applicationTester->getDisplay();

        $contains = " example\n"
            . "  example:dep  Test command with dependencies\n";

        self::assertSame(0, $statusCode);
        self::assertStringContainsString(
            $contains,
            $display,
            'Output does not contain: ' . "\n" . $contains . "\n" . '---' . "\n" . $display
        );
    }

    /**
     * @see https://github.com/laminas/laminas-cli/pull/28
     * @see https://github.com/laminas/laminas-cli/pull/29
     */
    public function testHelpDisplaysInformationForCommandWithDependencies(): void
    {
        $config = [
            'laminas-cli' => [
                'commands' => [
                    'example:dep' => ExampleCommandWithDependencies::class,
                ],
            ],
        ];

        /** @psalm-var ContainerInterface&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects($this->atLeast(2))
            ->method('has')
            ->willReturnMap([
                ['Laminas\Cli\SymfonyEventDispatcher', false],
                [ExampleCommandWithDependencies::class, true],
            ]);

        $container
            ->method('get')
            ->will($this->returnCallback(function (string $service) use ($config, $container) {
                switch ($service) {
                    case 'config':
                        return $config;
                    case ExampleDependency::class:
                        $factory = new ExampleDependencyFactory();
                        return $factory($container);
                    case ExampleCommandWithDependencies::class:
                        $factory = new ExampleCommandWithDependenciesFactory();
                        return $factory($container);
                    default:
                        return null;
                }
            }));

        $application = $this->createApplicationInstance($container);

        $applicationTester = new ApplicationTester($application);
        $statusCode        = $applicationTester->run([
            'command'      => 'help',
            'command_name' => 'example:dep',
        ]);

        $display = $applicationTester->getDisplay();

        $contains = [
            "Usage:\n  example:dep [options]\n",
            "  -s, --string=STRING        A string option [default: \"default value\"]\n",
            "Help:\n  Execute a test command that includes dependencies",
        ];

        self::assertSame(0, $statusCode);
        foreach ($contains as $str) {
            self::assertStringContainsString($str, $display);
        }
    }

    private function createApplicationInstance(ContainerInterface $container): Application
    {
        $applicationFactory = new ApplicationFactory();
        return (new ApplicationProvisioner())($applicationFactory(), $container);
    }
}
