<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Listener;

use Laminas\Cli\Input\Mapper\ArrayInputMapper;
use Laminas\Cli\Input\Mapper\InputMapperInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Webmozart\Assert\Assert;

use function array_search;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function preg_match;
use function sprintf;
use function strtolower;

use const PHP_EOL;

/**
 * @internal
 */
final class TerminateListener
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke(ConsoleTerminateEvent $event): void
    {
        if ($event->getExitCode() !== 0 || ! $event->getInput()->isInteractive()) {
            return;
        }

        $command = $event->getCommand();
        Assert::isInstanceOf($command, Command::class);

        $class = get_class($command);
        if (
            ! isset($this->config['chains'][$class])
            || ! is_array($this->config['chains'][$class])
        ) {
            return;
        }

        $chain = $this->config['chains'][$class];
        Assert::isMap($chain);

        $commands = $this->config['commands'];
        Assert::isMap($commands);

        $application = $command->getApplication();
        Assert::isInstanceOf($application, Application::class);

        $helper = $application->getHelperSet()->get('question');
        Assert::isInstanceOf($helper, QuestionHelper::class);

        $input  = $event->getInput();
        $output = $event->getOutput();

        /** @psalm-var array<string, string|array> $chain */
        foreach ($chain as $nextCommandClass => $inputMapperSpec) {
            $nextCommandName = array_search($nextCommandClass, $commands, true);
            Assert::string($nextCommandName, sprintf(
                'No command name found for chained command class "%s"; make sure it is defined'
                . ' in the laminas-cli.commands configuration',
                $nextCommandClass
            ));

            $nextCommand       = $application->find($nextCommandName);
            $thirdPartyMessage = (bool) preg_match('/^(Mezzio|Laminas)\b/', $nextCommandClass)
                ? ''
                : PHP_EOL . '<error>WARNING: This is a third-party command</error>';

            $question = new ChoiceQuestion(
                PHP_EOL . "<info>Executing {$nextCommandName}</info> ({$nextCommand->getDescription()})."
                . $thirdPartyMessage
                . PHP_EOL . '<question>Do you want to continue?</question>',
                ['Y' => 'yes, continue', 's' => 'skip command', 'n' => 'no, break'],
                'Y'
            );

            switch (strtolower((string) $helper->ask($input, $output, $question))) {
                case 's':
                    $output->writeln("Skipping {$nextCommandName}.");
                    continue 2;
                case 'n':
                    $output->writeln("Break on {$nextCommandName}.");
                    break 2;
            }

            $inputMapper = $this->createInputMapper($inputMapperSpec, $nextCommandClass);

            $params   = ['command' => $nextCommandName] + $inputMapper($input);
            $exitCode = $application->run(new ArrayInput($params), $output);

            if ($exitCode !== 0) {
                $event->setExitCode((int) $exitCode);
                return;
            }
        }
    }

    /**
     * @param mixed $inputMapperSpec
     */
    private function createInputMapper($inputMapperSpec, string $commandClass): InputMapperInterface
    {
        if (is_array($inputMapperSpec)) {
            $this->validateInputMap($inputMapperSpec, $commandClass);
            /** @psalm-var array<string|int, string|array<string, string>> $inputMapperSpec */
            return new ArrayInputMapper($inputMapperSpec);
        }

        Assert::string($inputMapperSpec, sprintf(
            'Expected array option map or %s class implementation name for %s input mapper; received "%s"',
            InputMapperInterface::class,
            $commandClass,
            is_object($inputMapperSpec) ? get_class($inputMapperSpec) : gettype($inputMapperSpec)
        ));

        Assert::classExists(
            $inputMapperSpec,
            sprintf('Input mapper for "%s" is of a class that does not exist ("%s")', $commandClass, $inputMapperSpec)
        );

        Assert::implementsInterface($inputMapperSpec, InputMapperInterface::class, sprintf(
            'Input mapper "%s" for command "%s" does not implement %s',
            $inputMapperSpec,
            $commandClass,
            InputMapperInterface::class
        ));

        return new $inputMapperSpec();
    }

    private function validateInputMap(array $inputMap, string $commandClass): void
    {
        foreach ($inputMap as $value) {
            if (is_string($value)) {
                // We have a single mapping
                continue;
            }
            // We have an array of mappings
            Assert::isMap($value, 'Malformed input mapper for ' . $commandClass);
            Assert::allString($value, 'Malformed input mapper for ' . $commandClass);
        }
    }
}
