# laminas-cli

Command-line interface for Laminas projects

## Installation

### Via Composer

Install the library globally using [Composer](https://getcomposer.org):

```bash
$ composer require laminas/laminas-cli
```

## Usage

```bash
$ vendor/bin/laminas-cli
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
