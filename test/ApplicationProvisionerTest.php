<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ApplicationProvisioner;
use Laminas\Cli\ContainerCommandLoader;
use Laminas\Cli\Listener\TerminateListener;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
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
}
