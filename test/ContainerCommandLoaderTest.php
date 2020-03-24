<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ContainerCommandLoader;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerCommandLoaderTest extends TestCase
{
    public function testGetCommandHasName()
    {
        $commands = [
            'foo-bar-command' => TestAsset\ExampleCommand::class,
        ];

        $container = $this->prophesize(ContainerInterface::class);
        $container->has(TestAsset\ExampleCommand::class)->willReturn(true);
        $container->get(TestAsset\ExampleCommand::class)->willReturn(new TestAsset\ExampleCommand());

        $loader = new ContainerCommandLoader($container->reveal(), $commands);

        $command = $loader->get('foo-bar-command');

        self::assertInstanceOf(TestAsset\ExampleCommand::class, $command);
        self::assertSame('foo-bar-command', $command->getName());
    }
}
