<?php

declare(strict_types=1);

namespace LaminasTest\Cli\Listener;

use Laminas\Cli\Listener\TerminateListener;
use LaminasTest\Cli\ApplicationTest;
use LaminasTest\Cli\TestAsset\ExampleCommand;
use Local\LocalCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psalm\Internal\PluginManager\Command\ShowCommand;
use Psalm\Internal\PluginManager\PluginListFactory;
use ReflectionMethod;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function getcwd;
use function is_dir;
use function opendir;
use function preg_match;
use function preg_replace;
use function readdir;
use function realpath;
use function rtrim;

class TerminateListenerTest extends TestCase
{
    /**
     * @var Command|MockObject
     * @psalm-var Command&MockObject
     */
    private $command;

    /**
     * @var InputInterface|MockObject
     * @psalm-var InputInterface&MockObject
     */
    private $input;

    /**
     * @var OutputInterface|MockObject
     * @psalm-var OutputInterface&MockObject
     */
    private $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = $this->createMock(Command::class);
        $this->input   = $this->createMock(InputInterface::class);
        $this->output  = $this->createMock(OutputInterface::class);
    }

    public function testSkipIfExitStatusIsNotZero(): void
    {
        $this->input->expects($this->never())->method('isInteractive');
        $this->command->expects($this->never())->method('getApplication');

        $listener = new TerminateListener(ApplicationTest::getValidConfiguration());
        $event    = new ConsoleTerminateEvent($this->command, $this->input, $this->output, 1);

        $listener($event);
    }

    public function testSkipIfNotInteractiveMode(): void
    {
        $this->input->expects($this->once())->method('isInteractive')->willReturn(true);
        $this->command->expects($this->never())->method('getApplication');

        $listener = new TerminateListener(ApplicationTest::getValidConfiguration());
        $event    = new ConsoleTerminateEvent($this->command, $this->input, $this->output, 0);

        $listener($event);
    }

    public function testSkipIfThereIsNoChain(): void
    {
        $this->input->expects($this->once())->method('isInteractive')->willReturn(true);
        $this->command->expects($this->never())->method('getApplication');

        $listener = new TerminateListener([]);
        $event    = new ConsoleTerminateEvent($this->command, $this->input, $this->output, 0);

        $listener($event);
    }

    public function testSkipIfChainConfigurationIsNotAnArray(): void
    {
        $this->input->expects($this->once())->method('isInteractive')->willReturn(true);
        $command = new ExampleCommand();

        $listener = new TerminateListener([
            'chains' => [
                ExampleCommand::class => true,
            ],
        ]);
        $event    = new ConsoleTerminateEvent($command, $this->input, $this->output, 0);

        $listener($event);
    }

    public function testNotifiesOfThirdPartyCommandInChain(): void
    {
        $listener = new TerminateListener([
            'commands' => [
                'example:command-name' => ExampleCommand::class,
                'psalm:show'           => ShowCommand::class,
            ],
            'chains'   => [
                ExampleCommand::class => [
                    ShowCommand::class => [],
                ],
            ],
        ]);

        $thirdPartyCommand = new ShowCommand(new PluginListFactory(
            getcwd(),
            getcwd() . '/vendor/vimeo/psalm'
        ));
        $r                 = new ReflectionMethod($thirdPartyCommand, 'configure');
        $r->setAccessible(true);
        $r->invoke($thirdPartyCommand);

        $this->input
            ->expects($this->once())
            ->method('isInteractive')
            ->willReturn(true);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Break'));

        $expectedChoiceQuestion = static fn(Question $question): bool =>
            (bool) preg_match('#<error>.*?This is a third-party command</error>#i', $question->getQuestion());

        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->equalTo($this->input),
                $this->equalTo($this->output),
                $this->callback($expectedChoiceQuestion)
            )
            ->willReturn('n');

        $helperSet = $this->createMock(HelperSet::class);
        $helperSet
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('question'))
            ->willReturn($questionHelper);

        $app = $this->createMock(Application::class);
        $app
            ->expects($this->atLeastOnce())
            ->method('getHelperSet')
            ->willReturn($helperSet);

        $app
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo('psalm:show'))
            ->willReturn($thirdPartyCommand);

        $command = new ExampleCommand();
        $command->setApplication($app);

        $event = new ConsoleTerminateEvent(
            $command,
            $this->input,
            $this->output,
            0
        );

        $this->assertNull($listener($event));
    }

    public function testDoesNotNotifyForLocalCommandInChain(): void
    {
        $listener = new TerminateListener([
            'commands' => [
                'example:command-name' => ExampleCommand::class,
                'local:command'        => LocalCommand::class,
            ],
            'chains'   => [
                ExampleCommand::class => [
                    LocalCommand::class => [],
                ],
            ],
        ]);

        $localCommand = new LocalCommand();
        $localCommand->configure();

        $this->input
            ->expects($this->once())
            ->method('isInteractive')
            ->willReturn(true);

        $expectedChoiceQuestion = static function (Question $question): bool {
            $query = $question->getQuestion();
            return ! preg_match('#<error>.*?This is a third-party command</error>#i', $query)
                && (bool) preg_match('#<info>Executing local:command</info>#i', $query);
        };

        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper
            ->expects($this->once())
            ->method('ask')
            ->with(
                $this->equalTo($this->input),
                $this->equalTo($this->output),
                $this->callback($expectedChoiceQuestion)
            )
            ->willReturn('y');

        $helperSet = $this->createMock(HelperSet::class);
        $helperSet
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('question'))
            ->willReturn($questionHelper);

        $app = $this->createMock(Application::class);
        $app
            ->expects($this->atLeastOnce())
            ->method('getHelperSet')
            ->willReturn($helperSet);

        $app
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo('local:command'))
            ->willReturn($localCommand);

        $command = new ExampleCommand();
        $command->setApplication($app);

        $event = new ConsoleTerminateEvent(
            $command,
            $this->input,
            $this->output,
            0
        );

        $this->assertNull($listener($event));
    }

    public function testVendorDirectoryCanBeResolvedViaComposerSetting(): void
    {
        $path         = realpath(__DIR__);
        $composerJson = <<<END
            {
                "config": {
                    "vendor-dir": "$path"
                }
            }
            END;

        $expected = rtrim(realpath(preg_replace('#\\\\#', '/', __DIR__)), '/') . '/';

        $listener = new TerminateListener([]);
        $r        = new ReflectionMethod($listener, 'getVendorDirectory');
        $r->setAccessible(true);

        $this->assertSame(
            $expected,
            $r->invoke($listener, $composerJson)
        );
    }

    /**
     * @psalm-return array<string, array{0: string}>
     */
    public function homeDirectorySpecifications(): array
    {
        return [
            '$HOME' => ['$HOME'],
            '~'     => ['~'],
        ];
    }

    /**
     * @dataProvider homeDirectorySpecifications
     */
    public function testVendorDirectorySpecifiedAsHomeInComposerSettingResolvesToHomeDirectory(string $spec): void
    {
        $composerJson = <<<END
            {
                "config": {
                    "vendor-dir": "$spec"
                }
            }
            END;

        $home = $_SERVER['HOME'] ?? null;
        Assert::string($home);

        $expected = rtrim(realpath(preg_replace('#\\\\#', '/', $home)), '/') . '/';
        $listener = new TerminateListener([]);
        $r        = new ReflectionMethod($listener, 'getVendorDirectory');
        $r->setAccessible(true);

        $this->assertSame(
            $expected,
            $r->invoke($listener, $composerJson)
        );
    }

    private function getFirstHomeSubdirectory(string $home): ?string
    {
        Assert::directory($home);
        $handle = opendir($home);
        while (false !== ($entry = readdir($handle))) {
            $path = $home . '/' . $entry;
            if (! preg_match('/^\.{1,2}$/', $entry) && is_dir($path)) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @dataProvider homeDirectorySpecifications
     */
    public function testVendorDirectoryStartingWithHomeInComposerSettingResolvesViaHomeDirectory(string $spec): void
    {
        $home = $_SERVER['HOME'] ?? null;
        Assert::string($home);

        $subdir = $this->getFirstHomeSubdirectory($home);
        if (null === $subdir) {
            $this->markTestSkipped('No $HOME subdirectory; cannot complete test');
        }

        $composerJson = <<<END
            {
                "config": {
                    "vendor-dir": "$spec/$subdir"
                }
            }
            END;

        $expected = rtrim(realpath(preg_replace('#\\\\#', '/', $home)), '/') . '/' . $subdir . '/';
        $listener = new TerminateListener([]);
        $r        = new ReflectionMethod($listener, 'getVendorDirectory');
        $r->setAccessible(true);

        $this->assertSame(
            $expected,
            $r->invoke($listener, $composerJson)
        );
    }
}
