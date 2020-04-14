<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Symfony\Component\Console\Command\Command;

class ExampleCommandWithDependencies extends Command
{
    /** @var string */
    protected static $defaultName = 'example:command-with-deps';

    /** @var ExampleDependency */
    private $dependency;

    public function __construct(ExampleDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
