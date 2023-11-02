<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class Chained2Command extends AbstractCommand
{
    /** @var string|null */
    protected static $commandName = 'example:chained-2';

    /** @var string */
    protected $argName = 'arg2';

    /** @var string */
    protected $optName = 'opt2';
}
