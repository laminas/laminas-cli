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
final class ContainerCommandLoaderNoTypeHint extends AbstractContainerCommandLoader
{
    /**
     * @param string $name
     */
    public function get($name): Command
    {
        return $this->getCommand($name);
    }

    /**
     * @param string $name
     */
    public function has($name): bool
    {
        return $this->hasCommand($name);
    }
}
