<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class ExampleDependency
{
    /** @var string */
    public $default;

    public function __construct(string $defaultValue)
    {
        $this->default = $defaultValue;
    }

    public function getDefault(): string
    {
        return $this->default;
    }
}
