<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerInterface;
use RuntimeException;

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
    public static function resolve() : ContainerInterface
    {
        if (file_exists('config/container.php')) {
            $container = include 'config/container.php';
            if ($container instanceof ContainerInterface) {
                return $container;
            }
        } elseif (file_exists('config/application.config.php')
            && class_exists(ServiceManager::class)
        ) {
            $appConfig = include 'config/application.config.php';
            if (file_exists('config/development.config.php')) {
                $appConfig = ArrayUtils::merge(
                    $appConfig,
                    include 'config/development.config.php'
                );
            }

            $smConfig = new ServiceManagerConfig($appConfig['service_manager'] ?? []);

            $serviceManager = new ServiceManager();
            $smConfig->configureServiceManager($serviceManager);
            $serviceManager->setService('ApplicationConfig', $appConfig);

            $serviceManager->get('ModuleManager')->loadModules();

            return $serviceManager;
        }

        throw new RuntimeException('Cannot detect DI container');
    }
}
