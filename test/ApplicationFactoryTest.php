<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ApplicationFactory;
use Laminas\Cli\Listener\TerminateListener;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/** @psalm-suppress PropertyNotSetInConstructor */
class ApplicationFactoryTest extends TestCase
{
    public function testPullsEventDispatcherFromContainerWhenPresent(): void
    {
        $config = [
            'laminas-cli' => [],
        ];

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with(
                ConsoleEvents::TERMINATE,
                $this->isInstanceOf(TerminateListener::class)
            );

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('has')
            ->with('Laminas\Cli\SymfonyEventDispatcher')
            ->willReturn(true);
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['config'],
                ['Laminas\Cli\SymfonyEventDispatcher']
            )
            ->willReturnOnConsecutiveCalls(
                $config,
                $dispatcher
            );

        $this->assertInstanceOf(Application::class, (new ApplicationFactory())($container));
    }
}
