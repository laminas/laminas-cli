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
    public const EXCEPTION_MESSAGE = 'ExampleDependency should not be fetched from container';

    /**
     * @throws Exception
     */
    public function __construct()
    {
        throw new Exception(self::EXCEPTION_MESSAGE);
    }
}
