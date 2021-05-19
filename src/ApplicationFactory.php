<?php

declare(strict_types=1);

namespace Laminas\Cli;

use PackageVersions\Versions;
use Symfony\Component\Console\Application;
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
    public const CONTAINER_OPTION = ContainerOptionFactory::CONTAINER_OPTION;

    public function __invoke(): Application
    {
        /** @psalm-suppress DeprecatedClass */
        $version = strstr(Versions::getVersion('laminas/laminas-cli'), '@', true);
        Assert::string($version);
        $application = new Application('laminas', $version);
        $application->setAutoExit(false);

        $definition = $application->getDefinition();
        $definition->addOption((new ContainerOptionFactory())());

        return $application;
    }
}
