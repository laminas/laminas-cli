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

use function array_keys;
use function class_exists;

/**
 * @internal
 */
abstract class AbstractContainerCommandLoader implements CommandLoaderInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @var string[] */
    private $commandMap;

    final public function __construct(ContainerInterface $container, array $commandMap)
    {
        $this->container  = $container;
        $this->commandMap = $commandMap;
    }

    protected function getCommand(string $name): Command
    {
        $command = $this->container->has($this->commandMap[$name])
            ? $this->container->get($this->commandMap[$name])
            : $this->createCommand($name);
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

        return new $class();
    }
}
