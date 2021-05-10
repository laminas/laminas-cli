<?php

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
