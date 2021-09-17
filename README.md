# laminas-cli

[![Build Status](https://github.com/laminas/laminas-cli/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/laminas/laminas-cli/actions/workflows/continuous-integration.yml)

Command-line interface for Laminas projects

## Installation

### Via Composer

Install the library using [Composer](https://getcomposer.org):

```bash
$ composer require laminas/laminas-cli
```

## Usage

```bash
$ vendor/bin/laminas [--container=<path>] [command-name]
```

## Custom Command

If you want to add a command for a Laminas MVC or Mezzio application, implement a standard [Symfony console](https://symfony.com/doc/current/components/console.html) command and register the command to use with laminas-cli via application configuration:

```php
return [
    'laminas-cli' => [
        'commands' => [
            'package:command-name' => MyCommand::class,
        ],
    ],
];
```

Please remember that if a command has any constructor dependencies, you should also map a factory for the command within the container.

### Configurations

#### Laminas MVC

For Laminas MVC applications, this would like like:

```php
return [
    'service_manager' => [
        'factories' => [
            MyCommand::class => MyCommandFactory::class,
        ],
    ],
];
```

#### Mezzio

For Mezzio applications, this would like like:

```php
return [
    'dependencies' => [
        'factories' => [
            MyCommand::class => MyCommandFactory::class,
        ],
    ],
];
```

### Custom Command Loader

In case you want to integrate commands from external command provider such as `doctrine/orm` commands, you might want to add a custom command loader.

You can do so by adding a new service to your configuration:

```php
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Laminas\Cli\CommandLoaderInterface;
use Laminas\Cli\ContainerCommandLoader;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface as SymfonyCommandLoaderInterface;
    
return [
    'dependencies' => [
        'factories' => [
            CommandLoaderInterface::class => static function (ContainerInterface $container): SymfonyCommandLoaderInterface {
                $entityManagerProvider = new SingleManagerProvider($container->get(EntityManagerInterface::class));
                $app = new Application();
                ConsoleRunner::addCommands($app, $entityManagerProvider);
                
                return new ContainerCommandLoader($container, $app->all());
            },
        ],
    ],
];
```

> This is a mezzio example, refer to [Laminas MVC](#laminas-mvc) if you want to provide a configuration in MVC applications.
