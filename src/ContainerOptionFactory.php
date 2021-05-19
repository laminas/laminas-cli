<?php

declare(strict_types=1);

namespace Laminas\Cli;

use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class ContainerOptionFactory
{
    public const CONTAINER_OPTION = 'container';

    public function __invoke(): InputOption
    {
        return new InputOption(
            self::CONTAINER_OPTION,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to a file which returns a PSR-11 container'
        );
    }
}
