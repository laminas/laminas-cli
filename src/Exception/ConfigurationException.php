<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Exception;

use RuntimeException;

use function sprintf;

final class ConfigurationException extends RuntimeException implements ExceptionInterface
{
    public static function withInvalidMappedCommandClass(string $commandName, string $targetClassName): self
    {
        return new self(sprintf(
            'The target command class "%1$s" for the command named "%2$s" is not a known class and has not been '
            . 'configured with a factory. Either create a factory for "%1$s" or or alter the command to target an '
            . 'existing class',
            $targetClassName,
            $commandName
        ));
    }
}
