<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Laminas\Cli\Exception\ConfigurationException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Webmozart\Assert\Assert;

use function array_keys;
use function class_exists;

/**
 * @internal
 */
abstract class AbstractContainerCommandLoader implements CommandLoaderInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @psalm-var array<string, string> */
    private $commandMap;

    final public function __construct(ContainerInterface $container, array $commandMap)
    {
        $this->container = $container;

        Assert::isMap($commandMap);
        Assert::allString($commandMap);
        $this->commandMap = $commandMap;
    }

    protected function getCommand(string $name): Command
    {
        $command = $this->container->has($this->commandMap[$name])
            ? $this->container->get($this->commandMap[$name])
            : $this->createCommand($name);

        Assert::isInstanceOf($command, Command::class);

        $command->setName($name);

        return $command;
    }

    protected function hasCommand(string $name): bool
    {
        if ($this->container->has($this->commandMap[$name])) {
            return true;
        }

        return isset($this->commandMap[$name]);
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }

    private function createCommand(string $name): Command
    {
        $class = $this->commandMap[$name];
        if (! class_exists($class)) {
            throw ConfigurationException::withInvalidMappedCommandClass($name, $class);
        }

        /** @psalm-suppress MixedMethodCall */
        $command = new $class();
        Assert::isInstanceOf($command, Command::class);

        return $command;
    }
}
