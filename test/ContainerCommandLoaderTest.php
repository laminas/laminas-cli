<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use laminas\cli\containercommandloader;
use Laminas\Cli\ContainerResolver;
use Laminas\Cli\LazyLoadingCommand;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

use function chdir;
use function getcwd;

class ContainerCommandLoaderTest extends TestCase
{
    public function testGetCommandHasName()
    {
        $commands = [
            'foo-bar-command' => TestAsset\ExampleCommand::class,
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->with(TestAsset\ExampleCommand::class)
            ->willReturn(true);
        $container
            ->method('get')
            ->with(TestAsset\ExampleCommand::class)
            ->willReturn(new TestAsset\ExampleCommand());

        $loader = new containercommandloader($container, $commands);

        $command = $loader->get('foo-bar-command');

        self::assertInstanceOf(Command::class, $command);
        self::assertSame('foo-bar-command', $command->getName());
    }

    public function testGetCommandReturnsLazyCommand()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/TestAsset');
        $container = ContainerResolver::resolve();
        chdir($cwd);

        $config = $container->get('ApplicationConfig');

        $loader = new containercommandloader($container, $config['laminas-cli']['commands']);

        $command = $loader->get('example:command-with-deps');

        self::assertInstanceOf(Command::class, $command);
        self::assertInstanceOf(LazyLoadingCommand::class, $command);
    }
}
