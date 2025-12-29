<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Cli\TestAsset\ExampleCommandWithDependencies;
use LaminasTest\Cli\TestAsset\ExampleCommandWithDependenciesFactory;
use LaminasTest\Cli\TestAsset\ExampleDependency;
use LaminasTest\Cli\TestAsset\ExampleDependencyFactory;

$config = [
    'laminas-cli'  => [
        'commands' => [
            'example:command-with-deps' => ExampleCommandWithDependencies::class,
        ],
    ],
    'dependencies' => [
        'factories' => [
            ExampleCommandWithDependencies::class => ExampleCommandWithDependenciesFactory::class,
            ExampleDependency::class              => ExampleDependencyFactory::class,
        ],
    ],
];

$config['dependencies']['services'] = ['config' => $config];

return new ServiceManager($config['dependencies']);
