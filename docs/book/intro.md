# Introduction

laminas-cli is a command-line interface for Laminas projects.

It provides seamless integration with Laminas MVC and Mezzio applications.

It is easily extensible by custom command. 

## Integration with component

If you'd like to add integration with laminas-cli into your components
you have to the following:

1. Use `laminas/laminas-cli` as dev dependency:

```bash
$ composer require --dev laminas/laminas-cli
```

2. Add the command to the library:

```php
namespace MyNamespace\Command;

use Symfony\Component\Console\Command\Command;

class MyCommand extends Command
{
    // ...
}
```

3. Register your command in the container:

```php
return [
    'dependencies' => [
        'invokables' => [
            MyNamespace\Command\MyCommand::class,   
        ],
    ],
];
```

and register the command for the cli tool:

```php
return [
    'laminas-cli' => [
        'commands' => [
            'package:command-name' => MyNamespace\Command\MyCommand::class,
        ],
    ],
];
```

If the component is providing ConfigProvider (for Mezzio applications)
please provide above inside:

```php
namespace MyNamespace;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'cli' => $this->getCliConfig(),
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getCliConfig() : array
    {
        return [
            Command\MyCommand::class,
        ];    
    }

    public function getDependencyConfig() : array
    {
        return [
            'invokables' => [
                Command\MyCommand::class,
            ],
        ];
    }
}
```

In case you want also provide `Module` class for Laminas MVC:

```php
namespace MyNamespace;

class Module
{
    public function getConfig() : array
    {
        $configProvider = new ConfigProvider();

        return [
            'cli' => $configProvider->getCliConfig(),
            'service_manager' => $configProvider->getDependencyConfig(),
        ];
    }
}
```

## Integration for Other Applications - Custom DI Container

laminas-cli supports [Laminas MVC](https://github.com/laminas/laminas-mvc-skeleton)
and [Mezzio](https://github.com/mezzio/mezzio-skeleton) application out of the box.
If you want to use the tool with different application or you have modified
the default configuration and container cannot be detected automatically 
you can still use the laminas-cli tool. You just need configure your PSR-11
container and provide it that way the tool can detect it.

Just create `config/container.php` file which will return an instance of
[PSR-11 Container](https://www.php-fig.org/psr/psr-11/) and it should be detected
by the tool.

## Usage

To list all available commands run:

```bash
$ vendor/bin/laminas-cli
```

To execute a specific command run:

```bash
$ vendor/bin/laminas-cli <command-name>
```
