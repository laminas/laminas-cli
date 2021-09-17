<?php

declare(strict_types=1);

namespace Laminas\Cli;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface as SymfonyCommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Webmozart\Assert\Assert;

use function array_keys;
use function class_exists;
use function sprintf;

/**
 * @internal
 */
abstract class AbstractContainerCommandLoader implements SymfonyCommandLoaderInterface
{
    /** @var ContainerInterface */
    private $container;

    /** @psalm-var array<string, string|Command> */
    private $commandMap;

    /** @var SymfonyCommandLoaderInterface|null */
    private $applicationCommandLoader;

    /** @psalm-param array<string, string|Command> $commandMap */
    final public function __construct(
        ContainerInterface $container,
        array $commandMap,
        ?SymfonyCommandLoaderInterface $applicationCommandLoader = null
    ) {
        $this->container = $container;

        Assert::isMap($commandMap);
        Assert::allString($commandMap);
        $this->commandMap               = $commandMap;
        $this->applicationCommandLoader = $applicationCommandLoader;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function getApplicationCommandLoader(): ?SymfonyCommandLoaderInterface
    {
        return $this->applicationCommandLoader;
    }

    protected function getCommand(string $name): Command
    {
        if ($this->applicationCommandLoader && $this->applicationCommandLoader->has($name)) {
            return $this->applicationCommandLoader->get($name);
        }

        $command = $this->commandMap[$name] ?? null;
        if ($command === null) {
            throw new CommandNotFoundException(sprintf('Command with name "%s" not found.', $name));
        }

        if ($command instanceof Command) {
            return $command;
        }

        if ($this->container->has($command)) {
            return $this->fetchCommandFromContainer($command, $name);
        }

        Assert::classExists(
            $command,
            sprintf('Command "%s" maps to class "%s", which does not exist', $name, $command)
        );
        /** @psalm-suppress DocblockTypeContradiction */
        Assert::subclassOf($command, Command::class, sprintf(
            'Command "%s" maps to class "%s", which does not extend %s',
            $name,
            $command,
            Command::class
        ));

        /** @psalm-var class-string<Command> $command */
        return $this->createCommand($command, $name);
    }

    protected function hasCommand(string $name): bool
    {
        if ($this->applicationCommandLoader && $this->applicationCommandLoader->has($name)) {
            return true;
        }

        if (! isset($this->commandMap[$name])) {
            return false;
        }

        $command = $this->commandMap[$name];
        if ($command instanceof Command) {
            return true;
        }

        if ($this->container->has($command)) {
            return true;
        }

        return class_exists($command);
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }

    private function fetchCommandFromContainer(string $serviceName, string $name): Command
    {
        $command = $this->container->get($serviceName);
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
