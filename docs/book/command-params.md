# Command Params

In default Symfony Console commands it is possible to define
[input arguments and options](https://symfony.com/doc/current/console/input.html).

You can still do that, but we encourage using input parameters,
to simply write interactive commands.

## Using input parameters

Internally input parameters behaves like options with the following modifications:

- all parameters are optional by default,
- in interactive mode, when getting value parameter value, and the value
  is not provided with the command call, user will be asked to provide the value,
- in non-interactive mode, default value is used instead, if the parameter value
  is not supplied, exception will be thrown,
- values provided in command call are validated the same way as question asked
  to provide the value by user,
- if value of the parameter is provided by user is set back in input options,
  so it can be passed to the next [command in chain](commands-chain.md) (if defined),
- there is no shortcut version of parameter, as we have for option.

Please consider the following command:

```php
use Laminas\Cli\Input\InputParam;
use Laminas\Cli\Input\InputParamTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HelloCommand extends Command
{
    use InputParamTrait;

    public static $defaultName = 'example:hello';

    protected function configure() : void
    {
        $this->setName(self::$defaultName);
        $this->addParam('name', 'Your name', InputParam::TYPE_STRING);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $name = $this->getParam('name');
        $output->writeln('Hello ' . $name . '!');

        return 0;
    }
}
```

As you see it is very similar to adding option. As noted before there is no shortcut,
so we always must use the full name.

Now, let what happens when we call the command:

```console
$ vendor/bin/laminas example:hello
Your name:
 > Michal

Hello Michal!
```

or, if you provide the param value with the command call:

```console
$ vendor/bin/laminas example:hello --name=Michal

Hello Michal!
```

As you can see, there is no question asked when the parameter value is provided on command call.

## Build-in types

### InputParam::TYPE_BOOL

```php
$this->addParam(
    'enable',
    'Enable something',
    InputParam::TYPE_BOOL,
    false, // if the param is required, ignored for TYPE_BOOL
    false // default value in interactive mode: true or false
);
```

If the option is not set in command line call, in interactive mode use is going to be asked for confirmation:

```console
Enable something? [y/N]
 > ...
```

Value of this parameter is normalized to `boolean` type.

### InputParam::TYPE_INT

```php
$this->addParam(
    'age',
    'Your age',
    InputParam::TYPE_INT,
    true, // if the param is required (answer in interactive mode, if param value is not supplied)
    null, // no default value
    [
        'min' => 0,
        'max' => 120,
    ]
);
```

With InputParam::TYPE_INT we can define two options for validator: `min` and `max` value.
Value of this parameter is normalized to `interger` type.

### InputParam::TYPE_STRING

```php
$this->addParam(
    'module',
    'Module name',
    InputParam::TYPE_STRING,
    true, // if the param is required (answer in interactive mode, if param value is not supplied)
    null, // no default value
    [
        'pattern' => '/^[A-Z][a-zA-Z0-9]{2,15}$/',
    ]
);
```

We can provide regular expression pattern to validate the value.
In the above example we are expecting module name starting from an uppercase letter,
then any letter or number, in total length of minimum 3 and maximum 16 characters.

### InputParam::TYPE_PATH

```php
$this->addParam(
    'dir',
    'Directory with modules',
    InputParam::TYPE_PATH,
    true, // if the param is required (answer in interactive mode, if param value is not supplied)
    'module',
    [
        'type' => 'dir', // 'dir', 'file' or null (for both)
        'existing' => true, // if path must exist, default false
        // @todo not supported yet, not sure if needed:
        'base' => getcwd(), // the base directory for the files/directories
    ]
);
```

### InputParam::TYPE_CHOICE

```php
$this->addParam(
    'mode',
    'Module mode',
    InputParam::TYPE_CHOICE,
    true, // if the param is required (answer in interactive mode, if param value is not supplied)
    'D', // default value to use 'development' mode
    [
        'haystack' => [
            'D' => 'development',
            'p' => 'production',
        ],
    ]
);
```

### InputParam::TYPE_CUSTOM

If any of above types are still not satisfying for your use case, you can define custom parameter type.

```php
use Laminas\Cli\Input\InputParam;
use Symfony\Component\Console\Question\Question;

$customQuestion = new Question('Please provide value for custom parameter:');
// some question modifications, like:
$customQuestion->setValidator(static function (string $value) { /* ... */ });
$customQuestion->setAutocompleterCallback(static function (string $answer) { /* ... */ });
$customQuestion->setNormalizer(static function (string $answer) { /* ... */ });
// etc...

$this->addParam(
    'custom',
    'Custom parameter',
    InputParam::TYPE_CUSTOM,
    true, // if the param is required
    null,
    [
        'question' => $customQuestion,
    ]
);
```

Please note that the validator and normalizer is going to be applied also on getting the value
of the parameter when provided in command line call.
