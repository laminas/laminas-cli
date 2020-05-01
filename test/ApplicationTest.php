<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Generator;
use Laminas\Cli\ApplicationFactory;
use LaminasTest\Cli\TestAsset\Chained1Command;
use LaminasTest\Cli\TestAsset\Chained2Command;
use LaminasTest\Cli\TestAsset\Chained3Command;
use LaminasTest\Cli\TestAsset\ExampleCommand;
use LaminasTest\Cli\TestAsset\InputMapper\CustomInputMapper;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;

use function array_filter;
use function current;
use function strpos;

class ApplicationTest extends TestCase
{
    public static function getValidConfiguration() : array
    {
        return [
            'commands' => [
                'example:command-name' => ExampleCommand::class,
                'example:chained-1' => Chained1Command::class,
                'example:chained-2' => Chained2Command::class,
                'example:chained-3' => Chained3Command::class,
            ],
            'chains' => [
                ExampleCommand::class => [
                    Chained1Command::class => ['arg' => 'arg1', '--opt' => '--opt1'],
                    Chained3Command::class => ['arg' => 'arg3', '--opt' => '--opt3'],
                ],
                Chained1Command::class => [
                    Chained2Command::class => ['arg1' => 'arg2', '--opt1' => '--opt2'],
                ],
            ],
        ];
    }

    private function getApplication(array $exitCodes = []) : Application
    {
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

        $applicationFactory = new ApplicationFactory();

        return $applicationFactory($container);
    }

    public function chainAnswer() : Generator
    {
        yield 'execute whole chain' => [
            ['y', 'y', 'y'],
            [
                ExampleCommand::class . ': arg=foo, opt=bar',
                Chained1Command::class . ': arg=foo, opt=bar',
                Chained2Command::class . ': arg=foo, opt=bar',
                Chained3Command::class . ': arg=foo, opt=bar',
            ],
            [],
        ];

        yield 'skip first chained' => [
            ['s', 'y'],
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
            ['y', 's', 'y'],
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
            ['y', 'y', 's'],
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
            ['y', 's', 's'],
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
            ['y', 'n', 'y'],
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
            ['y', 'n', 's'],
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
            ['y'],
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
            ['y', 'y'],
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
     *
     * @param string[] $answers
     * @param string[] $contains
     * @param string[] $doesNotContain
     * @param int[] $exitCodes
     */
    public function testChainCommand(array $answers, array $contains, array $doesNotContain, array $exitCodes = [])
    {
        $application = $this->getApplication($exitCodes);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs($answers);
        $statusCode = $applicationTester->run(
            [
                'command' => 'example:command-name',
                'arg' => 'foo',
                '--opt' => 'bar',
            ],
            [
                'interactive' => true,
            ]
        );

        self::assertSame(current(array_filter($exitCodes)) ?: 0, $statusCode);
        $display = $applicationTester->getDisplay();
        foreach ($contains as $str) {
            self::assertNotFalse(strpos($display, $str), 'Output does not contain ' . $str . "\n" . $display);
        }
        foreach ($doesNotContain as $str) {
            self::assertFalse(strpos($display, $str), 'Output contains ' . $str . "\n" . $display);
        }
    }

    public function testPassCustomParams()
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
                            'example:chained-1' => Chained1Command::class,
                        ],
                        'chains' => [
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

        $applicationFactory = new ApplicationFactory();
        $application = $applicationFactory($container);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs(['y']);
        $statusCode = $applicationTester->run(
            [
                'command' => 'example:command-name',
                'arg' => 'foo',
                '--opt' => 'bar',
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
            self::assertNotFalse(strpos($display, $str), 'Output does not contain ' . $str . "\n" . $display);
        }
    }

    public function testCustomInputMapper()
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
                            'example:chained-1' => Chained1Command::class,
                        ],
                        'chains' => [
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

        $applicationFactory = new ApplicationFactory();
        $application = $applicationFactory($container);

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs(['y']);
        $statusCode = $applicationTester->run(
            [
                'command' => 'example:command-name',
                'arg' => 'foo',
                '--opt' => 'bar',
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
            self::assertNotFalse(strpos($display, $str), 'Output does not contain ' . $str . "\n" . $display);
        }
    }

    public function testList()
    {
        $application = $this->getApplication();

        $applicationTester = new ApplicationTester($application);
        $applicationTester->setInputs(['y']);
        $statusCode = $applicationTester->run(['command' => 'list']);

        $display = $applicationTester->getDisplay();

        $contains = ' example' . "\n"
            . '  example:chained-1     Description of example:chained-1' . "\n"
            . '  example:chained-2     Description of example:chained-2' . "\n"
            . '  example:chained-3     Description of example:chained-3' . "\n"
            . '  example:command-name  Description of example:command-name' . "\n";

        self::assertSame(0, $statusCode);
        self::assertNotFalse(
            strpos($display, $contains),
            'Output does not contain: ' . "\n" . $contains . "\n" . '---' . "\n" . $display
        );
    }
}
