<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use InvalidArgumentException;
use Laminas\ModuleManager\ModuleManagerInterface;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Assert\Assert;

use function assert;
use function class_exists;
use function file_exists;
use function is_string;
use function sprintf;

/**
 * @internal
 */
final class ContainerResolver
{
    /**
     * @var string
     * @psalm-var non-empty-string
     */
    private $projectRoot;

    /**
     * @psalm-param non-empty-string $projectRoot
     */
    public function __construct(string $projectRoot)
    {
        $this->projectRoot = $projectRoot;
    }

    /**
     * Try to find container in Laminas application.
     * Supports out of the box Laminas MVC and Mezzio applications.
     *
     * @throws RuntimeException When cannot locate PSR-11 container for the application.
     */
    public function resolve(InputInterface $input): ContainerInterface
    {
        if ($input->hasOption(ApplicationFactory::CONTAINER_OPTION)) {
            $pathToContainer = $input->getOption(ApplicationFactory::CONTAINER_OPTION);
            assert(is_string($pathToContainer) && $pathToContainer !== '');

            // Verify if an absolute path was passed
            if (! file_exists($pathToContainer)) {
                $pathToContainer = sprintf('%s/%s', $this->projectRoot, $pathToContainer);
                assert($pathToContainer !== '');
            }

            return $this->resolveContainerFromPath($pathToContainer);
        }

        $mezzioContainer = sprintf('%s/config/container.php', $this->projectRoot);
        assert($mezzioContainer !== '');

        if (file_exists($mezzioContainer)) {
            return $this->resolveContainerFromPath($mezzioContainer);
        }

        $applicationConfiguration = sprintf('%s/config/application.config.php', $this->projectRoot);
        assert($applicationConfiguration !== '');
        if (
            file_exists($applicationConfiguration)
            && class_exists(ServiceManager::class)
        ) {
            return $this->resolveMvcContainer($applicationConfiguration);
        }

        throw new RuntimeException(
            sprintf(
                'Cannot detect PSR-11 container to configure the laminas-cli application.'
                . ' You can use the --%s option to provide a file which returns a PSR-11 container instance.',
                ApplicationFactory::CONTAINER_OPTION
            )
        );
    }

    /**
     * @psalm-param non-empty-string $path
     */
    private function resolveMvcContainer(string $path): ContainerInterface
    {
        /**
         * @psalm-suppress UnresolvableInclude
         * @psalm-var array<array-key, mixed> $appConfig
         */
        $appConfig = include $path;
        Assert::isMap($appConfig);

        $developmentConfigPath = sprintf('%s/config/development.config.php', $this->projectRoot);
        if (file_exists($developmentConfigPath)) {
            /**
             * @psalm-var array<array-key, mixed> $devConfig
             */
            $devConfig = include $developmentConfigPath;
            Assert::isMap($devConfig);

            /** @psalm-var array<array-key, mixed> $appConfig */
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

    /**
     * @psalm-param non-empty-string $containerPath
     */
    private function resolveContainerFromPath(string $containerPath): ContainerInterface
    {
        if (! file_exists($containerPath)) {
            throw new InvalidArgumentException('Provided path must be relative to the project root.');
        }

        $container = include $containerPath;
        Assert::isInstanceOf($container, ContainerInterface::class, 'Failed to load PSR-11 container');

        return $container;
    }
}
