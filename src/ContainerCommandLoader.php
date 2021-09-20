<?php

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\Command\Command;

/**
 * @internal
 */
final class ContainerCommandLoader extends AbstractContainerCommandLoader
{
    public function has(string $name): bool
    {
        return $this->hasCommand($name);
    }

    public function get(string $name): Command
    {
        return $this->getCommand($name);
    }
}
