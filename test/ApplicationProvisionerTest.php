<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ApplicationProvisioner;
use Laminas\Cli\CommandLoaderInterface;
use Laminas\Cli\ContainerCommandLoader;
use Laminas\Cli\Listener\TerminateListener;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface as SymfonyCommandLoaderInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ApplicationProvisionerTest extends TestCase
{
    public function testWillConfigureApplication(): void
    {
        $application = $this->createMock(Application::class);

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
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap([
                ['config', true],
                ['Laminas\Cli\SymfonyEventDispatcher', true],
            ]);

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

        $application
            ->expects(self::once())
            ->method('setDispatcher')
            ->with($dispatcher);

        $application
            ->expects(self::once())
            ->method('setCommandLoader')
            ->with(self::callback(static function (ContainerCommandLoader $loader) use ($container): bool {
                self::assertEquals($loader->getContainer(), $container);
                return true;
            }));

        (new ApplicationProvisioner())($application, $container);
    }

    public function testCanHandleEmptyContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::never())
            ->method('get');

        $container
            ->method('has')
            ->willReturn(false);

        $application = $this->createMock(Application::class);

        (new ApplicationProvisioner())($application, $container);
    }

    public function testWillPassCommandLoaderFromContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->willReturnMap([
                [CommandLoaderInterface::class, true],
            ]);

        $commandLoader = $this->createMock(CommandLoaderInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with(CommandLoaderInterface::class)
            ->willReturn($commandLoader);

        $application = $this->createMock(Application::class);
        $application
            ->method('setCommandLoader')
            ->willReturnCallback(static function (SymfonyCommandLoaderInterface $loader) use ($commandLoader): void {
                self::assertInstanceOf(ContainerCommandLoader::class, $loader);
                self::assertSame($loader->getApplicationCommandLoader(), $commandLoader);
            });

        (new ApplicationProvisioner())($application, $container);
    }
}
