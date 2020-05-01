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

use function strstr;

/**
 * @internal
 */
final class ApplicationFactory
{
    public function __invoke(ContainerInterface $container) : Application
    {
        $config = $container->get('config')['laminas-cli'] ?? [];

        $version = strstr(Versions::getVersion('laminas/laminas-cli'), '@', true);

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(ConsoleEvents::TERMINATE, new TerminateListener($config));

        $application = new Application('laminas', $version);
        $application->setCommandLoader(new ContainerCommandLoader($container, $config['commands'] ?? []));
        $application->setDispatcher($dispatcher);
        $application->setAutoExit(false);

        return $application;
    }
}
