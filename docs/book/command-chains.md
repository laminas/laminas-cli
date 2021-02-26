# Command chains

Sometimes you may want to execute another command straight after the successful
completion of another command. As an example, consider the following two
command classes:

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

We can expose each to laminas-cli, and also create a _chain_, whereby when the
first command finishes execution, it will then invoke the second:

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

<!-- markdownlint-disable-next-line MD001 -->
> ### "chains" configuration options
>
> We discuss [chain configuration in more detail below](#chain-configuration).

Running `./vendor/bin/laminas first-command` will result with:

```console
$ ./vendor/bin/laminas first-command --name=Foo
First command: Foo

Executing second-command. Do you want to continue?
  [Y] yes, continue
  [s] skip this command,
  [n] no, break

> yes, continue

Second command: Foo
```

Please note that only successful result of the first command will trigger the
second command.  The final result (exit code) of the chained commands will be
the result of the last executed command. If a command in the middle of the chain
results in a failure status, execution will halt with that command, and its
status will be returned.

## Chain configuration

Chain configuration is under the "chains" section of the "laminas-cli"
configuration:

```php
<?php
return [
    'laminas-cli' => [
        'chains' => [ /* . . . */ ],
    ],
];
```

The configuration is expected to be an associative array mapping command names
you have previously defined in the "commands" section of the "laminas-cli"
configuration, and the value is an associative array:

```php
'chains' => [
    COMMAND_CLASS_NAME => CHAINED_COMMANDS
],
```

The chained commands (`CHAINED_COMMANDS`) are themselves an associative array,
where the key is the name of a command you have already defined in the
"commands" section of the "laminas-cli" configuration, and the value is an
associative array:

```php
'chains' => [
    COMMAND_CLASS_NAME => [
        CHAINED_COMMAND_CLASS_NAME => INPUT_MAPPER,
    ],
],
```

An input mapper (`INPUT_MAPPER`) can be one of two things:

- a string class name of an implemention of `Laminas\Cli\Input\Mapper\InputMapperInterface`
- an array specification

Most commonly, you will use an array specification. In this case, items can take
two forms:

- a key/value pair, where the key is the option or argument from the previous
  command, and the value is the option or argument by which to provide the value
  to the chained command.
- an array, with a single key/value pair of the option or argument name on the
  chained command, and the value to use with it.

As a visualization:

```php
'chains' => [
    COMMAND_CLASS_NAME => [
        'argument-on-previous-command' => 'argument-on-this-command',
        '--option-on-previous-command' => '--option-on-this-command',
        ['an-argument-on-this-command' => 'argument value to supply'],
        ['--an-option-on-this-command' => 'option value to supply'],
    ],
],
```

When specifying options, the `--` prefix should be used with the option names,
just like you'd invoke them from the command line if you were to call the
command by itself.

### Chain command input mapper example

If we return to the original example from the first section of this page:

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

We have defined two commands, `FirstCommand` and `SecondCommand`. `FirstCommand`
defines the option `--name`, while `SecondCommand` defines the option
`--module`. In the above configuration, we indicate that when we call
`FirstCommand`, we want to start a command chain that also invokes
`SecondCommand`. When it does so, it should take the value provided via the
`--name` option and pass that value to the `SecondCommand` `--module` option. In
effect, that would be similar to calling the following commands in sequence:

```bash
$ ./vendor/bin/laminas first-command --name Foo
$ ./vendor/bin/laminas second-command --module Foo
```

Since `FirstCommand` now provides a chain, we can call:

```bash
$ ./vendor/bin/laminas first-command --name Foo
```

and `Foo` will be passed for the `--module` option when `SecondCommand` is
invoked as part of the chain.

## InputMapperInterface

As noted in the previous section, you can provide a string class name of a
`Laminas\Cli\Input\Mapper\InputMapperInterface` implementation to use in order
to map arguments and options from one command to another. That interface defines
one method:

```php
namespace Laminas\Cli\Input\Mapper;

use Symfony\Component\Console\Input\InputInterface;

interface InputMapperInterface
{
    public function __invoke(InputInterface $input): array;
}
```

The return value should be an associative array mapping arguments and options to
the values they contain, suitable for use with
`Symfony\Component\Console\Input\ArrayInput` ([see symfony/console documentation
for details](https://symfony.com/doc/current/console/calling_commands.html));

## Example

The initial section of this page

```php
[
   'name'   => 'module', // adds "module" argument to the next command call with the value of "name" argument from the previous command
   '--mode' => '--type', // adds "--type" option to the next command call with the value of "--mode" option from the previous command
   ['additional-arg'   => 'arg-value'], // adds "additional-arg" argument to the next command call with the value "arg-value"
   ['--additional-opt' => 'opt-value'], // adds "--additional-opt" option to the next command call with the value "opt-value"
],
```

It is also possible to provide class name (string) which implements `Laminas\Cli\Input\Mapper\InputMapperInterface`
if you need more customised mapper between input of the previous and next command.
