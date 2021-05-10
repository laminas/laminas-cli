<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ExampleCommandWithDependenciesFactory
{
    public function __invoke(ContainerInterface $container): ExampleCommandWithDependencies
    {
        $dependency = $container->get(ExampleDependency::class);
        Assert::isInstanceOf($dependency, ExampleDependency::class);
        return new ExampleCommandWithDependencies($dependency);
    }
}
