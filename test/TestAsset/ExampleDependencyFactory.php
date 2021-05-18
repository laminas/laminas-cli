<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Psr\Container\ContainerInterface;

class ExampleDependencyFactory
{
    public function __invoke(ContainerInterface $container): ExampleDependency
    {
        return new ExampleDependency('default value');
    }
}
