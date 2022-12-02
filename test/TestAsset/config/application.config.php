<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

return [
    'modules'                 => [],
    'module_listener_options' => [],
    'laminas-cli'             => [
        'commands' => [
            'example:command-with-deps' => ExampleCommandWithDependencies::class,
        ],
    ],
    'service_manager'         => [
        'factories' => [
            ExampleCommandWithDependencies::class => ExampleCommandWithDependenciesFactory::class,
            ExampleDependency::class              => ExampleDependencyFactory::class,
        ],
    ],
];
