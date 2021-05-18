<?php

declare(strict_types=1);

namespace Laminas\Cli;

use PackageVersions\Versions;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

use function strstr;

/**
 * This factory is not registered in the container on purpose.
 * We do not want expose Laminas\Cli as a Module/ConfigProvider.
 * It is just for internal use.
 *
 * @internal
 */
final class ApplicationFactory
{
    public const CONTAINER_OPTION = 'container';

    public function __invoke(): Application
    {
        /** @psalm-suppress DeprecatedClass */
        $version = strstr(Versions::getVersion('laminas/laminas-cli'), '@', true);
        Assert::string($version);
        $application = new Application('laminas', $version);
        $application->setAutoExit(false);

        $definition = $application->getDefinition();
        $definition->addOption(
            new InputOption(
                self::CONTAINER_OPTION,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to a file which returns a PSR-11 container'
            )
        );

        return $application;
    }
}
