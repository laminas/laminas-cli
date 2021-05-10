<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class Chained1Command extends AbstractCommand
{
    /** @var string|null */
    protected static $defaultName = 'example:chained-1';

    /** @var string */
    protected $argName = 'arg1';

    /** @var string */
    protected $optName = 'opt1';
}
