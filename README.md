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

## Custom command

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
