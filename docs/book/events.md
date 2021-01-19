# Events

The [symfony/console component](https://symfony.com/doc/current/components/console.html) allows attaching an event dispatcher instance to a console application.
During the lifetime of a console command, the application will trigger a number of events, to which you may subscribe listeners.
Internally, laminas/laminas-cli itself adds a listener on the `Symfony\Component\Console\ConsoleEvents::TERMINATE` event in order to provide [command chains](command-chains.md).

If you wish to subscribe to any of the various symfony/console events, you will need to provide an alternate event dispatcher instance.
You may do so by defining a `Laminas\Cli\SymfonyEventDispatcher` service in your container that resolves to a `Symfony\Component\EventDispatcher\EventDispatcherInterface` instance. (We use this instead of the more generic `Symfony\Contracts\EventDispatcher\EventDispatcherInterface` so that we can use its `addListener()` method to subscribe our own listener.)

As an example, your container configuration file might look like the following for laminas-mvc applications.
Create a configuration file named `config/autoload/console.global.php` if it does not already exist, and ensure the following contents are present:

```php
return [
    'service_manager' => [
        'factories' => [
            'Laminas\Cli\SymfonyEventDispatcher' => \Your\Custom\DispatcherFactory::class,
            // ...
        ],
        'delegators' => [
            'Laminas\Cli\SymfonyEventDispatcher' => [
                // [OPTIONAL] Delegator factories for adding listeners and/or subscribers
            ],
            // ...
        ],
        // ...
    ],
    // ...
];
```

If you are in a Mezzio application, again, create a configuration file named `config/autoload/console.global.php` if it does not already exist, and ensure the following contents are present:

```php
return [
    'dependencies' => [
        'factories' => [
            'Laminas\Cli\SymfonyEventDispatcher' => \Your\Custom\DispatcherFactory::class,
            // ...
        ],
        'delegators' => [
            'Laminas\Cli\SymfonyEventDispatcher' => [
                // [OPTIONAL] Delegator factories for adding listeners and/or subscribers
            ],
            // ...
        ],
        // ...
    ],
    // ...
];
```
