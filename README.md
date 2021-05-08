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

if you want to add any command for Laminas MVC or Mezzio application just implement normal
Symfony console command and add register the command for the cli:

```php
return [
    'laminas-cli' => [
        'commands' => [
            'package:command-name' => MyCommand::class,
        ],
    ],
];
```

Please remember that if command has some dependencies you should register also factory within
the container, for example:

```php
return [
    'dependencies' => [
        'factories' => [
            MyCommand::class => MyCommandFactory::class,
        ],
    ],
];
```
