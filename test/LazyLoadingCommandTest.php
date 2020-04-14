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
use LaminasTest\Cli\TestAsset\ExampleCommandWithDependencies;
use LaminasTest\Cli\TestAsset\ExampleDependency;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

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
        self::expectExceptionMessage(ExampleDependency::$exceptionMessage);

        $command->run($input, $output);
    }
}
