<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

/**
 * @internal
 */
final class ContainerCommandLoaderNoTypeHint implements CommandLoaderInterface
{
    private $container;
    private $commandMap;

    public function __construct(ContainerInterface $container, array $commandMap)
    {
        $this->container = $container;
        $this->commandMap = $commandMap;
    }

    public function get($name) : LazyLoadingCommand
    {
        return new LazyLoadingCommand($name, $this->commandMap[$name], $this->container);
    }

    public function has($name)
    {
        return isset($this->commandMap[$name]) && $this->container->has($this->commandMap[$name]);
    }

    public function getNames()
    {
        return array_keys($this->commandMap);
    }
}
