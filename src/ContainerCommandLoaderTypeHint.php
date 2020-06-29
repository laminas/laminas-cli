<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\Command\Command;

/**
 * @internal
 */
final class ContainerCommandLoaderTypeHint extends AbstractContainerCommandLoader
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
