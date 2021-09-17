<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Laminas\Cli\Listener\TerminateListener;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface as SymfonyCommandLoaderInterfaceAlias;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ApplicationProvisioner
{
    public function __invoke(Application $application, ContainerInterface $container): Application
    {
        $config = $container->has('config')
            ? $container->get('config')['laminas-cli'] ?? []
            : [];
        Assert::isMap($config);

        $commands = $config['commands'] ?? [];
        Assert::isMap($commands);
        Assert::allString($commands);

        $eventDispatcherServiceName = __NAMESPACE__ . '\SymfonyEventDispatcher';
        $dispatcher                 = $container->has($eventDispatcherServiceName)
            ? $container->get($eventDispatcherServiceName)
            : new EventDispatcher();
        Assert::isInstanceOf($dispatcher, EventDispatcherInterface::class);

        $applicationCommandLoaderServiceName = CommandLoaderInterface::class;
        $applicationCommandLoader            = null;
        if ($container->has($applicationCommandLoaderServiceName)) {
            /** @psalm-suppress MixedAssignment */
            $applicationCommandLoader = $container->get($applicationCommandLoaderServiceName);
            Assert::isInstanceOf($applicationCommandLoader, SymfonyCommandLoaderInterfaceAlias::class);
        }

        $dispatcher->addListener(ConsoleEvents::TERMINATE, new TerminateListener($config));

        $application->setCommandLoader(new ContainerCommandLoader(
            $container,
            $commands,
            $applicationCommandLoader
        ));
        $application->setDispatcher($dispatcher);

        return $application;
    }
}
