<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader as SymfonyContainerCommandLoader;

/**
 * @internal
 */
final class ContainerCommandLoader extends SymfonyContainerCommandLoader
{
    public function get(string $name) : Command
    {
        $command = parent::get($name);
        $command->setName($name);

        return $command;
    }
}
