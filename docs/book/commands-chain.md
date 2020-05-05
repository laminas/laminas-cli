# Commands chain

Sometimes we want to execute another command straight after successful run of a command.
Please consider the following example:

`MyApp\Command\FirstCommand.php`:

```php
namespace MyApp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FirstCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'first-command';

    protected function configure() : void
    {
        $this->setName(self::$defaultName);
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Module name');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('First command: ' . $input->getOption('name'));

        return 0;
    }
}
```

`MyApp\Command\SecondCommand.php`:

```php
namespace MyApp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SecondCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'second-command';

    protected function configure() : void
    {
        $this->setName(self::$defaultName);
        $this->addOption('module', null, InputOption::VALUE_REQUIRED, 'Module name');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Second command: ' . $input->getOption('module'));

        return 0;
    }
}
```

Then we need only configure the chain:

```php
namespace MyApp\Command;

return [
    'laminas-cli' => [
        'commands' => [
            'first-command'  => Command\FirstCommand::class,
            'second-command' => Command\SecondCommand::class,
        ],
        'chains' => [
            Command\FirstCommand::class => [
                Command\SecondCommand::class => ['--name' => '--module'],
            ],
        ],
    ],
];
```

> #### Chain configuration
>
> ```php
> 'chains' => [
>     TriggerCommand::class => [
>         ChainCommand1::class => [input mapper definition between TriggerCommand and ChainCommand1],
>         // ...
>         ChainCommandN::class => [input mapper definition between TriggerCommand and ChainCommandN],
>     ],
> ],
> ```
>
> Input mapper definition is the way how options and arguments of the trigger command should be mapped
> into input for the chained command. For options `--` prefix should be used with names, the same how
> we define arguments in symfony console application.
>
> It is also possible to add additional argument or option to the next command. Just provide an array
> as the value, for example:
>
> ```php
> [
>    'name'   => 'module', // adds "module" argument to the next command call with the value of "name" argument from the previous command
>    '--mode' => '--type', // adds "--type" option to the next command call with the value of "--mode" option from the previous command
>    ['additional-arg'   => 'arg-value'], // adds "additional-arg" argument to the next command call with the value "arg-value"
>    ['--additional-opt' => 'opt-value'], // adds "--additional-opt" option to the next command call with the value "opt-value"
> ],
> ```
>
> It is also possible to provide class name (string) which implements `Laminas\Cli\Input\Mapper\InputMapperInterface`
> if you need more customised mapper between input of the previous and next command.

Now running `vendor/bin/laminas first-command` will result with:

```console
$ vendor/bin/laminas first-command --name=Foo
First command: Foo

Executing second-command. Do you want to continue?
  [Y] yes, continue
  [s] skip this command,
  [n] no, break

> yes, continue

Second command: Foo
```

Please note that only successful result of the first command will trigger the second command.
The final result (exit code) of the chained commands will be the result of the last executed command.
