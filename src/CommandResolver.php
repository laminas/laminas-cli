<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use RuntimeException;
use Symfony\Component\Console\Command\Command;

use function gettype;
use function is_a;
use function is_string;
use function sprintf;

/**
 * @internal
 */
final class CommandResolver
{
    /** @var string[] */
    private $config;

    /**
     * @param string[] $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string[]
     * @throws RuntimeException When invalid command class provided.
     */
    public function resolve() : array
    {
        $commands = [];
        foreach ($this->config as $commandClass) {
            if (! is_string($commandClass)
                || ! is_a($commandClass, Command::class, true)
            ) {
                throw new RuntimeException(sprintf(
                    'Invalid command provided: %s',
                    is_string($commandClass) ? $commandClass : gettype($commandClass)
                ));
            }

            $commands[$commandClass::getDefaultName()] = $commandClass;
        }

        return $commands;
    }
}
