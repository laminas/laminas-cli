<?php

declare(strict_types=1);

namespace Laminas\Cli;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_keys;
use function sprintf;

/**
 * @internal
 */
abstract class AbstractContainerCommandLoader implements CommandLoaderInterface
{
    /** @psalm-var array<string, string> */
    private $commandMap;

    /** @psalm-param array<string, string> $commandMap */
    final public function __construct(private ContainerInterface $container, array $commandMap)
    {
        Assert::isMap($commandMap);
        Assert::allString($commandMap);
        $this->commandMap = $commandMap;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function getCommand(string $name): Command
    {
        if ($this->container->has($this->commandMap[$name])) {
            return $this->fetchCommandFromContainer($name);
        }

        $class = $this->commandMap[$name];
        Assert::classExists($class, sprintf('Command "%s" maps to class "%s", which does not exist', $name, $class));
        /** @psalm-suppress DocblockTypeContradiction */
        Assert::subclassOf($class, Command::class, sprintf(
            'Command "%s" maps to class "%s", which does not extend %s',
            $name,
            $class,
            Command::class
        ));

        /** @psalm-var class-string<Command> $class */
        return $this->createCommand($class, $name);
    }

    protected function hasCommand(string $name): bool
    {
        if (! array_key_exists($name, $this->commandMap)) {
            return false;
        }

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

    private function fetchCommandFromContainer(string $name): Command
    {
        $command = $this->container->get($this->commandMap[$name]);
        Assert::isInstanceOf($command, Command::class);
        $command->setName($name);
        return $command;
    }

    /** @psalm-param class-string<Command> $class */
    private function createCommand(string $class, string $name): Command
    {
        /** @psalm-suppress UnsafeInstantiation */
        $command = new $class();
        $command->setName($name);
        return $command;
    }
}
