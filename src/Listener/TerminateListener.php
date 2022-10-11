<?php

declare(strict_types=1);

namespace Laminas\Cli\Listener;

use Laminas\Cli\Input\Mapper\ArrayInputMapper;
use Laminas\Cli\Input\Mapper\InputMapperInterface;
use ReflectionClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Webmozart\Assert\Assert;

use function array_search;
use function file_get_contents;
use function getcwd;
use function gettype;
use function is_array;
use function is_int;
use function is_object;
use function is_string;
use function json_decode;
use function preg_match;
use function preg_replace;
use function realpath;
use function rtrim;
use function sprintf;
use function str_starts_with;
use function strtolower;

use const PHP_EOL;

/**
 * @internal
 */
final class TerminateListener
{
    private const ALLOWED_VENDORS = [
        'laminas',
        'laminas-api-tools',
        'mezzio',
    ];

    private const HOME_PATH_REGEX = '#^(~|\$HOME)#';

    public function __construct(private array $config)
    {
    }

    public function __invoke(ConsoleTerminateEvent $event): void
    {
        if ($event->getExitCode() !== 0 || ! $event->getInput()->isInteractive()) {
            return;
        }

        $command = $event->getCommand();
        Assert::isInstanceOf($command, Command::class);

        $class = $command::class;
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

        $vendorDir = $this->getVendorDirectory();
        $input     = $event->getInput();
        $output    = $event->getOutput();

        /** @psalm-var array<class-string, string|array> $chain */
        foreach ($chain as $nextCommandClass => $inputMapperSpec) {
            $nextCommandName = array_search($nextCommandClass, $commands, true);
            Assert::string($nextCommandName, sprintf(
                'No command name found for chained command class "%s"; make sure it is defined'
                . ' in the laminas-cli.commands configuration',
                $nextCommandClass
            ));

            $nextCommand       = $application->find($nextCommandName);
            $thirdPartyMessage = $this->matchesApplicationClass($nextCommandClass, $vendorDir)
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
            /** @psalm-suppress TypeDoesNotContainType */
            if (! is_int($exitCode)) {
                $exitCode = 0;
            }

            if ($exitCode !== 0) {
                $event->setExitCode($exitCode);
                return;
            }
        }
    }

    private function createInputMapper(mixed $inputMapperSpec, string $commandClass): InputMapperInterface
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
            is_object($inputMapperSpec) ? $inputMapperSpec::class : gettype($inputMapperSpec)
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

    /**
     * @psalm-return non-empty-string
     */
    private function getVendorDirectory(?string $composerJson = null): string
    {
        $basePath = getcwd();
        if (null === $composerJson) {
            $composerJson = file_get_contents($basePath . '/composer.json');
            Assert::string($composerJson);
        }

        $composer = json_decode($composerJson, true);
        Assert::isMap($composer);

        $vendorDir = $composer['config']['vendor-dir'] ?? $basePath . '/vendor';
        Assert::string($vendorDir);

        $vendorDir = $this->resolveHomePath($vendorDir);
        Assert::directory($vendorDir);

        $vendorDir = $this->normalizePath(realpath($vendorDir));
        return rtrim($vendorDir, '/') . '/';
    }

    /**
     * @psalm-param class-string $class
     */
    private function matchesApplicationClass(string $class, string $vendorDir): bool
    {
        $r = new ReflectionClass($class);

        $filename = $r->getFileName();
        Assert::string($filename, sprintf(
            'Cannot determine file where command class "%s" is declared; is it really a command?',
            $class
        ));

        $filename = $this->normalizePath($filename);
        if (! str_starts_with($filename, $vendorDir)) {
            return true;
        }

        foreach (self::ALLOWED_VENDORS as $vendor) {
            $path = $vendorDir . $vendor . '/';
            if (str_starts_with($filename, $path)) {
                // Matches a Laminas or Mezzio command name
                return true;
            }
        }

        return false;
    }

    private function normalizePath(string $path): string
    {
        return preg_replace('#\\\\#', '/', $path);
    }

    /**
     * Resolve references to the HOME directory.
     *
     * Composer allows you to specify the strings "~" or "$HOME" within the
     * config.vendor-dir setting. If so specified, it will replace those values
     * with the value of the $HOME path.
     *
     * This routine detects the usage of one of those strings, replacing it with
     * the value of $_SERVER['HOME'] if it exists. If not, it returns the
     * $directory argument verbatim.
     */
    private function resolveHomePath(string $directory): string
    {
        if (! preg_match(self::HOME_PATH_REGEX, $directory)) {
            return $directory;
        }

        if (! isset($_SERVER['HOME'])) {
            return $directory;
        }

        /** @psalm-suppress RedundantCondition */
        Assert::string($_SERVER['HOME']);

        $updated = preg_replace(self::HOME_PATH_REGEX, $_SERVER['HOME'], $directory);
        Assert::string($updated);

        return $updated;
    }
}
