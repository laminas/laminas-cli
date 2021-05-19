<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

/**
 * @internal
 */
interface ContainerInputInterface
{
    public function get(): string;
}
