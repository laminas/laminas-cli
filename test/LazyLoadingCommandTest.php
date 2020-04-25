<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Exception;
use Laminas\Cli\ContainerResolver;
use Laminas\Cli\LazyLoadingCommand;
use LaminasTest\Cli\TestAsset\ExampleCommand;
use LaminasTest\Cli\TestAsset\ExampleCommandWithDependencies;
use LaminasTest\Cli\TestAsset\ExampleDependency;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use function chdir;
use function getcwd;

class LazyLoadingCommandTest extends TestCase
{
    public function testDoesNotLoadDependenciesBeforeNeededButStillHasSameName()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/TestAsset');
        $container = ContainerResolver::resolve();
        chdir($cwd);

        $commandName = 'example:command-with-deps';

        $command = new LazyLoadingCommand($commandName, ExampleCommandWithDependencies::class, $container);

        self::assertEquals($command->getName(), $commandName);
    }

    public function testLoadsDependenciesWhenExecuted()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/TestAsset');
        $container = ContainerResolver::resolve();
        chdir($cwd);

        $commandName = 'example:command-with-deps';

        $command = new LazyLoadingCommand($commandName, ExampleCommandWithDependencies::class, $container);

        $input = new ArrayInput([]);
        $output = new NullOutput();

        self::expectException(Exception::class);
        self::expectExceptionMessage(ExampleDependency::EXCEPTION_MESSAGE);

        $command->run($input, $output);
    }

    public function testWillPassApplicationToCommandBeforeExecuting()
    {
        $container = $this->createMock(ContainerInterface::class);

        $commandName = 'example:command';
        $application = new Application();

        $lazyCommand = new LazyLoadingCommand($commandName, ExampleCommand::class, $container);

        $command = new ExampleCommand();
        $container
            ->expects($this->once())
            ->method('get')
            ->with(ExampleCommand::class)
            ->willReturn($command);

        $lazyCommand->setApplication($application);

        $input = new ArrayInput([]);
        $output = new NullOutput();

        $lazyCommand->run($input, $output);

        self::assertEquals($application, $command->getApplication());
    }
}
