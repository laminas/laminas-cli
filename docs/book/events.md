# Events

The [symfony/console component](https://symfony.com/doc/current/components/console.html) allows attaching an event dispatcher instance to a console application.
During the lifetime of a console command, the application will trigger a number of events, to which you may subscribe listeners.
Internally, laminas/laminas-cli itself adds a listener on the `Symfony\Component\Console\ConsoleEvents::TERMINATE` event in order to provide [command chains](command-chains.md).

If you wish to subscribe to any of the various [symfony/console events](https://symfony.com/doc/current/components/console/events.html), you will need to provide an alternate event dispatcher instance.
You may do so by defining a `Laminas\Cli\SymfonyEventDispatcher` service in your container that resolves to a `Symfony\Component\EventDispatcher\EventDispatcherInterface` instance. (We use this instead of the more generic `Symfony\Contracts\EventDispatcher\EventDispatcherInterface` so that we can use its `addListener()` method to subscribe our own listener.)

As an example, let's say you want to register the `Symfony\Component\Console\EventListener\ErrorListener` in your console application for purposes of debugging.
First, we will create a factory for this listener in the file `src/App/ConsoleErrorListenerFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventListener\ErrorListener;

final class ConsoleErrorListenerFactory
{
    public function __invoke(ContainerInterface $container): ErrorListener
    {
        return new ErrorListener($container->get(LoggerInterface::class));
    }
}
```

> The above example assumes you have already wired the `Psr\Log\LoggerInterface` service in your container configuration.

Next, we will create the class `App\ConsoleEventDispatcherFactory` in the file `src/App/ConsoleEventDispatcherFactory.php`.
The factory will create an `EventDispatcher` instance, attach the error listener, and return the dispatcher.

```php
<?php

declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventListener\ErrorListener;

final class ConsoleEventDispatcherFactory
{
    public function __invoke(ContainerInterface $container): EventDispatcher
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener($container->get(ErrorListener::class));

        return $dispatcher;
    }
}
```

Finally, we need to wire both our `ErrorListener` and our `EventDispatcher` services in our container.
We can do so by creating a configuration file named `config/autoload/console.global.php` if it does not already exist, and adding the following contents:

```php
<?php

return [
    '{CONTAINER_KEY}' => [
        'factories' => [
            'Laminas\Cli\SymfonyEventDispatcher' => \App\ConsoleEventDispatcherFactory::class,
            \Symfony\Component\EventDispatcher\EventListener\ErrorListener::class => \App\ConsoleErrorListenerFactory::class,
            // ...
        ],
        // ...
    ],
    // ...
];
```

> For the value of `{CONTAINER_KEY}`, substitute the following:
>
> - For laminas-mvc applications, use the value "service_manager".
> - For Mezzio applications, use the value "dependencies".

Later, if you want to register other listeners, you can either update your `App\ConsoleEventDispatcherFactory`, or you can add [delegator factories](https://docs.laminas.dev/laminas-servicemanager/delegators/) on the "Laminas\Cli\SymfonyEventDispatcher" service.

Read the [symfony/console events documentation](https://symfony.com/doc/current/components/console/events.html) more information on how to add listeners to the event dispatcher.
