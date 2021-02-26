<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

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
