<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class Chained3Command extends AbstractCommand
{
    /** @var string|null */
    protected static $commandName = 'example:chained-3';

    /** @var string */
    protected $argName = 'arg3';

    /** @var string */
    protected $optName = 'opt3';
}
