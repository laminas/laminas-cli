<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\CommandResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommandResolverTest extends TestCase
{
    public function testResolve()
    {
        $commands = [
            TestAsset\ExampleCommand::class,
        ];

        $commandsResolver = new CommandResolver($commands);

        self::assertSame(
            ['laminas-cli:test:example' => TestAsset\ExampleCommand::class],
            $commandsResolver->resolve()
        );
    }

    public function testInvalidCommand()
    {
        $commands = [
            TestAsset\ExampleCommand::class,
            'invalidCommand',
        ];

        $commandsResolver = new CommandResolver($commands);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid command provided: invalidCommand');
        $commandsResolver->resolve();
    }
}
