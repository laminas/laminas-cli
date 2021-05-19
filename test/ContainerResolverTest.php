<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use bovigo\vfs\vfsStream;
use Laminas\Cli\ContainerResolver;
use Laminas\Cli\Input\ContainerInputInterface;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Cli\TestAsset\ExampleDependency;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function sprintf;
use function sys_get_temp_dir;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ContainerResolverTest extends TestCase
{
    public function testWillLoadContainerFromInputOption(): void
    {
        $containerFileContents = sprintf(<<<EOT
            <?php return new \Laminas\ServiceManager\ServiceManager();
        EOT);

        $containerPath = 'container.php';
        /** @psalm-suppress InvalidArgument */
        $directory = vfsStream::setup('root', null, [
            $containerPath => $containerFileContents,
        ]);

        $input = $this->createMock(ContainerInputInterface::class);

        $input
            ->expects(self::once())
            ->method('get')
            ->willReturn($containerPath);

        $projectRoot = $directory->url();
        self::assertNotSame($projectRoot, '');
        /** @psalm-var non-empty-string $projectRoot */
        $resolver = new ContainerResolver($projectRoot);
        $resolver->resolve($input);
    }

    public function testWillLoadContainerFromApplicationConfig(): void
    {
        $input = $this->createMock(ContainerInputInterface::class);

        $resolver  = new ContainerResolver(__DIR__ . '/TestAsset');
        $container = $resolver->resolve($input);
        self::assertInstanceOf(ServiceManager::class, $container);
        self::assertTrue($container->has(ExampleDependency::class));
    }

    public function testWillLoadContainerFromMezzioContainerPath(): void
    {
        $containerFileContents = sprintf(<<<EOT
            <?php \$container = new \Laminas\ServiceManager\ServiceManager();
            \$container->setService('foo', 'bar');
            return \$container;
        EOT);

        $directory = vfsStream::setup('root', null, [
            'config' => [
                'container.php' => $containerFileContents,
            ],
        ]);

        $input = $this->createMock(ContainerInputInterface::class);

        $projectRoot = $directory->url();
        self::assertNotSame($projectRoot, '');
        /** @psalm-var non-empty-string $projectRoot */
        $resolver  = new ContainerResolver($projectRoot);
        $container = $resolver->resolve($input);
        self::assertTrue($container->has('foo'));
    }

    public function testCanHandleAbsolutePathForContainerOption(): void
    {
        $containerFileContents = sprintf(<<<EOT
            <?php return new \Laminas\ServiceManager\ServiceManager();
        EOT);

        $containerFileName = 'container.php';
        /** @psalm-suppress InvalidArgument */
        $directory = vfsStream::setup('root', null, [
            $containerFileName => $containerFileContents,
        ]);

        $containerPath = sprintf('%s/%s', $directory->url(), $containerFileName);
        $input         = $this->createMock(ContainerInputInterface::class);

        $input
            ->expects(self::once())
            ->method('get')
            ->willReturn($containerPath);

        $projectRoot = $directory->url();
        self::assertNotSame($projectRoot, '');
        /** @psalm-var non-empty-string $projectRoot */
        $resolver = new ContainerResolver($projectRoot);
        $resolver->resolve($input);
    }

    public function testWillThrowRuntimeExceptionWhenNoContainerCouldBeDetected(): void
    {
        $tempDirectory = sys_get_temp_dir();
        if ($tempDirectory === '') {
            self::fail('Temporary directory not available.');
        }

        $resolver = new ContainerResolver($tempDirectory);
        $input    = $this->createMock(ContainerInputInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot detect PSR-11 container');
        $resolver->resolve($input);
    }
}
