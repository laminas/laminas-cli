<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

use function class_exists;
use function file_exists;

/**
 * @internal
 */
final class ContainerResolver
{
    /**
     * Try to find container in Laminas application.
     * Supports out of the box Laminas MVC and Mezzio applications.
     *
     * @throws RuntimeException When cannot locate PSR-11 container for the application.
     */
    public static function resolve(): ContainerInterface
    {
        if (file_exists('config/container.php')) {
            return self::resolveDefaultContainer();
        }

        if (
            file_exists('config/application.config.php')
            && class_exists(ServiceManager::class)
        ) {
            return self::resolveMvcContainer();
        }

        throw new RuntimeException('Cannot detect PSR-11 container');
    }

    /**
     * @throws RuntimeException When file contains not a valid PSR-11 container.
     */
    private static function resolveDefaultContainer(): ContainerInterface
    {
        /**
         * @psalm-suppress MissingFile
         */
        $container = include 'config/container.php';

        Assert::isInstanceOf($container, ContainerInterface::class, 'Failed to load PSR-11 container');
        return $container;
    }

    private static function resolveMvcContainer(): ContainerInterface
    {
        /**
         * @psalm-suppress MissingFile
         * @psalm-var array<int|string, mixed> $appConfig
         */
        $appConfig = include 'config/application.config.php';
        Assert::isMap($appConfig);

        if (file_exists('config/development.config.php')) {
            /**
             * @psalm-suppress MissingFile
             * @psalm-var array<int|string, mixed> $devConfig
             */
            $devConfig = include 'config/development.config.php';
            Assert::isMap($devConfig);

            /** @psalm-var array<int|string, mixed> $appConfig */
            $appConfig = ArrayUtils::merge($appConfig, $devConfig);
            Assert::isMap($appConfig);
        }

        $servicesConfig = $appConfig['service_manager'] ?? [];
        Assert::isMap($servicesConfig);

        $smConfig = new ServiceManagerConfig($servicesConfig);

        $serviceManager = new ServiceManager();
        $smConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $appConfig);

        $moduleManager = $serviceManager->get('ModuleManager');
        Assert::isInstanceOf($moduleManager, ModuleManagerInterface::class);
        $moduleManager->loadModules();

        return $serviceManager;
    }
}
