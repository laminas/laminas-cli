<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class ExampleCommand extends AbstractCommand
{
    /** @var string|null */
    protected static $defaultName = 'example:command-name';

    /** @var string */
    protected $argName = 'arg';

    /** @var string */
    protected $optName = 'opt';
}
