<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Exception;

class ExampleDependency
{
    public static $exceptionMessage = 'ExampleDependency should not be fetched from container';

    public function __construct()
    {
        throw new Exception(self::$exceptionMessage);
    }
}
