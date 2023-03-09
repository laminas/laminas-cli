# Introduction

laminas-cli is a command-line interface for Laminas projects.

It provides seamless integration with Laminas MVC and Mezzio applications.

Both third-party packages and your own application can extend it by writing
commands and exposing them to the laminas-cli binary via configuration.

## Integrating in Components

If you'd like to add integration with laminas-cli into your components,
you will need to do the following:

1. Add `laminas/laminas-cli` as a dev dependency:

    ```bash
    $ composer require --dev laminas/laminas-cli
    ```

2. Create a command class in your library:

    ```php
    namespace MyNamespace\Command;
    
    use Symfony\Component\Console\Command\Command;
    
    class MyCommand extends Command
    {
        // ...
    }
    ```

3. If your command has dependencies, register the command and its factory in the
   container. Commands that can be instantiated with no constructor arguments
   can omit container configuration:

    ```php
    // config/autoload/dependencies.global.php:
    return [
        'dependencies' => [
            'factories' => [
                MyNamespace\Command\MyCommand::class => MyNamespace\Command\MyCommandFactory::class,
            ],
        ],
    ];
    ```

4. Register the command with the CLI tooling:

    ```php
    // config/autoload/global.php:
    return [
        'laminas-cli' => [
            'commands' => [
                'package:command-name' => MyNamespace\Command\MyCommand::class,
            ],
        ],
    ];
    ```

### Register Command in an Application with `ConfigProvider` Class like Mezzio

If your component is providing a `ConfigProvider` (such as in Mezzio
applications), please provide the configuration in that class instead:

```php
namespace MyNamespace;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'laminas-cli' => $this->getCliConfig(),
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getCliConfig() : array
    {
        return [
            'commands' => [
                'package:command-name' => Command\MyCommand::class,
            ],
        ];
    }

    public function getDependencyConfig() : array
    {
        return [
            'factories' => [
                Command\MyCommand::class => Command\MyCommandFactory::class,
            ],
        ];
    }
}
```

### Register Command in a laminas-mvc Application

If you want to provide a `Module` class for Laminas MVC, provide a
`ConfigProvider` as noted above, and then add the following `Module` class
declaration:

```php
namespace MyNamespace;

class Module
{
    public function getConfig() : array
    {
        $configProvider = new ConfigProvider();

        return [
            'laminas-cli' => $configProvider->getCliConfig(),
            'service_manager' => $configProvider->getDependencyConfig(),
        ];
    }
}
```

### MVC Application bootstrapping

Until 1.9.x laminas-cli did not bootrap the MVC Application and therefore left the Application in an unready state
wehen executing commands via laminas-cli. (read this [Issue](https://github.com/laminas/laminas-cli/issues/106))

To allow bootstrapping the MVC Application, without breaking backward compatibility, the option `'bootstrap_mvc_application'`
was introduced. Currently it's `false` by default to not break any Apps. This might change in the future.

When enabling it, make sure not to do any HTTP-Only related stuff on Module's `onBootstrap` method.

```php
// File config/application.config.php

<?php

return [
    /* ... */
    'laminas-cli' => [
        // execute Laminas\Mvc\Application::init(), including ::boostrap() during initialization of cli app
        'bootstrap_mvc_application' =>  true, 
    ]
];
```

## Integration in Other Applications

laminas-cli supports [Laminas MVC](https://github.com/laminas/laminas-mvc-skeleton)
and [Mezzio](https://github.com/mezzio/mezzio-skeleton) applications out of the box.
If you want to use the tool with a different application type, or you have modified
the default configuration and your [PSR-11 container](https://www.php-fig.org/psr/psr-11)
cannot be detected automatically, you can still use the laminas-cli tool.

To integrate such applications with laminas-cli, you will need to create a file
that returns a PSR-11 container.
Do so in the file `config/container.php` or if you already have such a file in another location, use the `--container=<path>` option.
Such a file might look like the following:

```php
<?php
// File config/container.php

declare(strict_types=1);

use JSoumelidis\SymfonyDI\Config\Config;
use JSoumelidis\SymfonyDI\Config\ContainerFactory;

$config  = require realpath(__DIR__) . '/../var/config.php';
$factory = new ContainerFactory();

return $factory(new Config($config));
```

Once such a file is in place, the laminas-cli binary will be able to use your
container to seed its application.

## Usage

### List

To list all available commands, run:

```bash
$ ./vendor/bin/laminas [--container=<path>]
```

### Execute

To execute a specific command, run:

```bash
$ ./vendor/bin/laminas [--container=<path>] <command-name>
```

### Help

To get help on a single command, run:

```bash
$ ./vendor/bin/laminas [--container=<path>] help <command-name>
```
