<?php

declare(strict_types=1);

namespace Laminas\Cli;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Assert\Assert;

use function file_exists;
use function sprintf;
use function str_contains;

/**
 * @internal
 *
 * @psalm-internal Laminas\Cli
 * @psalm-internal LaminasTest\Cli
 */
final readonly class ContainerResolver
{
    /**
     * @psalm-param non-empty-string $projectRoot
     */
    public function __construct(
        private string $projectRoot
    ) {
    }

    /**
     * Try to find container in Laminas application.
     * Supports out of the box Laminas MVC and Mezzio applications.
     *
     * @throws RuntimeException When cannot locate PSR-11 container for the application.
     */
    public function resolve(InputInterface $input): ContainerInterface
    {
        $pathToContainer = $input->getOption(ApplicationFactory::CONTAINER_OPTION) ?? '';
        Assert::string($pathToContainer);

        if ($pathToContainer !== '') {
            if (! $this->isAbsolutePath($pathToContainer)) {
                $pathToContainer = sprintf('%s/%s', $this->projectRoot, $pathToContainer);
                Assert::stringNotEmpty($pathToContainer);
            }

            return $this->resolveContainerFromAbsolutePath($pathToContainer);
        }

        $mezzioContainer = sprintf('%s/config/container.php', $this->projectRoot);
        Assert::stringNotEmpty($mezzioContainer);

        if (file_exists($mezzioContainer)) {
            return $this->resolveContainerFromAbsolutePath($mezzioContainer);
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
     * @psalm-param non-empty-string $containerPath
     */
    private function resolveContainerFromAbsolutePath(string $containerPath): ContainerInterface
    {
        if (! file_exists($containerPath)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Provided container path could not be resolved to an existing file: %s',
                    $containerPath
                )
            );
        }

        $container = include $containerPath;
        Assert::isInstanceOf($container, ContainerInterface::class, 'Failed to load PSR-11 container');

        return $container;
    }

    /**
     * Verifies that the provided path does not contain an absolute path.
     * Absolute paths can be either start with `/` or provided as an URI.
     *
     * @psalm-param non-empty-string $pathToContainer
     */
    private function isAbsolutePath(string $pathToContainer): bool
    {
        if ($pathToContainer[0] === '/') {
            return true;
        }

        if (str_contains($pathToContainer, '://')) {
            return true;
        }

        return false;
    }
}
