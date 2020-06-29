<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ContainerCommandLoader;
use Laminas\Cli\ContainerResolver;
use Laminas\Cli\Exception\ConfigurationException;
use LaminasTest\Cli\TestAsset\ExampleCommand;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

use function chdir;
use function getcwd;

class ContainerCommandLoaderTest extends TestCase
{
    public function testGetCommandHasName()
    {
        $commands = [
            'foo-bar-command' => TestAsset\ExampleCommand::class,
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('has')
            ->with(TestAsset\ExampleCommand::class)
            ->willReturn(true);
        $container
            ->method('get')
            ->with(TestAsset\ExampleCommand::class)
            ->willReturn(new TestAsset\ExampleCommand());

        $loader = new ContainerCommandLoader($container, $commands);

        $command = $loader->get('foo-bar-command');

        self::assertInstanceOf(Command::class, $command);
        self::assertSame('foo-bar-command', $command->getName());
    }

    public function testGetCommandReturnsCommand()
    {
        $cwd = getcwd();
        chdir(__DIR__ . '/TestAsset');
        $container = ContainerResolver::resolve();
        chdir($cwd);

        $config = $container->get('ApplicationConfig');

        $loader = new ContainerCommandLoader($container, $config['laminas-cli']['commands']);

        $command = $loader->get('example:command-with-deps');

        self::assertInstanceOf(Command::class, $command);
    }

    public function testHasWillReturnTrueWhenTheCommandIsMappedButNotPresentInTheContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with('CommandClassName')
            ->willReturn(false);

        $loader = new ContainerCommandLoader($container, [
            'my:command' => 'CommandClassName',
        ]);

        self::assertTrue($loader->has('my:command'));
    }

    public function testCommandWillBeConstructedWhenNotPresentInTheContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with(ExampleCommand::class)
            ->willReturn(false);

        $container->expects(self::never())
            ->method('get')
            ->with(ExampleCommand::class);

        $loader = new ContainerCommandLoader($container, [
            'my:command' => ExampleCommand::class,
        ]);

        $command = $loader->get('my:command');

        self::assertInstanceOf(ExampleCommand::class, $command);
    }

    public function testAnExceptionIsThrownWhenACommandAbsentFromTheContainerCannotBeConstructed(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('has')
            ->with('UnknownCommandClass')
            ->willReturn(false);

        $loader = new ContainerCommandLoader($container, [
            'my:command' => 'UnknownCommandClass',
        ]);

        try {
            $loader->get('my:command');
        } catch (ConfigurationException $exception) {
            $message = $exception->getMessage();

            $this->assertStringContainsString('my:command', $message);
            $this->assertStringContainsString('UnknownCommandClass', $message);

            return;
        }

        $this->fail('An exception was not thrown');
    }
}
