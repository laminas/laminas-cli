<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Listener;

use Laminas\Cli\Input\Mapper\ArrayInputMapper;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ChoiceQuestion;

use function array_search;
use function get_class;
use function is_array;
use function preg_match;
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
        $class   = get_class($command);

        if (
            ! isset($this->config['chains'][$class])
            || ! is_array($this->config['chains'][$class])
        ) {
            return;
        }

        $application = $command->getApplication();
        $input       = $event->getInput();
        $output      = $event->getOutput();

        /** @var QuestionHelper $helper */
        $helper = $application->getHelperSet()->get('question');

        foreach ($this->config['chains'][$class] as $nextCommandClass => $inputMapper) {
            $nextCommandName = array_search($nextCommandClass, $this->config['commands'], true);
            $nextCommand     = $application->find($nextCommandName);

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

            switch (strtolower($helper->ask($input, $output, $question))) {
                case 's':
                    $output->writeln("Skipping {$nextCommandName}.");
                    continue 2;
                case 'n':
                    $output->writeln("Break on {$nextCommandName}.");
                    break 2;
            }

            $inputMapper = is_array($inputMapper)
                ? new ArrayInputMapper($inputMapper)
                : new $inputMapper();

            $params   = ['command' => $nextCommandName] + $inputMapper($input);
            $exitCode = $application->run(new ArrayInput($params), $output);

            if ($exitCode !== 0) {
                $event->setExitCode($exitCode);
                return;
            }
        }
    }
}
