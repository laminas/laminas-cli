<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

class ExampleDependency
{
    public function __construct(public string $default)
    {
    }

    public function getDefault(): string
    {
        return $this->default;
    }
}
