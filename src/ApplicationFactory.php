<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Laminas\Cli\Listener\TerminateListener;
use PackageVersions\Versions;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Webmozart\Assert\Assert;

use function strstr;

/**
 * @internal This factory is not registered in the container on purpose.
 *     We do not want expose Laminas\Cli as a Module/ConfigProvider.
 *     It is just for internal use.
 */
final class ApplicationFactory
{
    public function __invoke(ContainerInterface $container): Application
    {
        $config = $container->get('config')['laminas-cli'] ?? [];
        Assert::isMap($config);

        /** @psalm-suppress DeprecatedClass */
        $version = strstr(Versions::getVersion('laminas/laminas-cli'), '@', true);
        Assert::string($version);

        $commands = $config['commands'] ?? [];
        Assert::isMap($commands);
        Assert::allString($commands);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(ConsoleEvents::TERMINATE, new TerminateListener($config));

        $application = new Application('laminas', $version);
        // phpcs:ignore WebimpressCodingStandard.PHP.CorrectClassNameCase
        $application->setCommandLoader(new ContainerCommandLoader($container, $commands));
        $application->setDispatcher($dispatcher);
        $application->setAutoExit(false);

        return $application;
    }
}
