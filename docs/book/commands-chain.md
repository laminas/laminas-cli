# Commands chain

Sometimes we want to execute another command straight after successful run of a command.
Please consider the following example:

`MyApp\Command\FirstCommand.php`:

```php
namespace MyApp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FirstCommand extends Command 
{
    protected static $defaultName = 'first-command';

    protected function configure() : void
    {
        $this->setName(self::$defaultName);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('First command.');
 
        return 0;
    }
}
``` 

`MyApp\Command\SecondCommand.php`:

```php
namespace MyApp\Command;

use Laminas\Cli\CommandListenerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class FirstCommand extends Command implements CommandListenerInterface
{
    protected static $defaultName = 'second-command';

    protected function configure() : void
    {
        $this->setName(self::$defaultName);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Second command.');
 
        return 0;
    }

    public function __invoke(ConsoleEvent $event) : int
    {
        $input = $event->getInput();
        $output = $event->getOutput();

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion('Do you want to run Second command? [Y/n] ');

        if ($helper->ask($input, $output, $question)) {
            return $this->run($input, $output);
        }

        return 0; 
    }
} 
```

and configuration:

```php
namespace MyApp\Command;

return [
    'laminas-cli' => [
        'commands' => [
            'first-command'  => Command\FirstCommand::class,
            'second-command' => Command\SecondCommand::class, 
        ],
        'listeners' => [
            Command\FirstCommand::class => [
                Command\SecondCommand::class,
            ],   
        ],
    ],
];
```

Now running `vendor/bin/laminas first-command` will result with:

```console
$ vendor/bin/laminas first-command
First command.
Do you want to run Second command? [Y/n] ___
Second command.
```

Please note that only successful result of the first command will trigger the second command.
The final result (exit code) of the chained commands will be the result of the last executed command.
