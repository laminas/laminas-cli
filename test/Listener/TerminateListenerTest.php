<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Listener;

use Laminas\Cli\Listener\TerminateListener;
use LaminasTest\Cli\ApplicationTest;
use LaminasTest\Cli\TestAsset\ExampleCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use ThirdParty\Console\ThirdPartyCommand;

use function preg_match;

class TerminateListenerTest extends TestCase
{
    /** @var MockObject|Command */
    private $command;

    /** @var MockObject|InputInterface */
    private $input;

    /** @var MockObject|OutputInterface */
    private $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = $this->createMock(Command::class);
        $this->input   = $this->createMock(InputInterface::class);
        $this->output  = $this->createMock(OutputInterface::class);
    }

    public function testSkipIfExitStatusIsNotZero()
    {
        $this->input->expects($this->never())->method('isInteractive');
        $this->command->expects($this->never())->method('getApplication');

        $listener = new TerminateListener(ApplicationTest::getValidConfiguration());
        $event    = new ConsoleTerminateEvent($this->command, $this->input, $this->output, 1);

        $listener($event);
    }

    public function testSkipIfNotInteractiveMode()
    {
        $this->input->expects($this->once())->method('isInteractive')->willReturn(true);
        $this->command->expects($this->never())->method('getApplication');

        $listener = new TerminateListener(ApplicationTest::getValidConfiguration());
        $event    = new ConsoleTerminateEvent($this->command, $this->input, $this->output, 0);

        $listener($event);
    }

    public function testSkipIfThereIsNoChain()
    {
        $this->input->expects($this->once())->method('isInteractive')->willReturn(true);
        $this->command->expects($this->never())->method('getApplication');

        $listener = new TerminateListener([]);
        $event    = new ConsoleTerminateEvent($this->command, $this->input, $this->output, 0);

        $listener($event);
    }

    public function testSkipIfChainConfigurationIsNotAnArray()
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
                'third-party:command'  => ThirdPartyCommand::class,
            ],
            'chains'   => [
                ExampleCommand::class => [
                    ThirdPartyCommand::class => [],
                ],
            ],
        ]);

        $thirdPartyCommand = new ThirdPartyCommand();
        $thirdPartyCommand->configure();

        $this->input
            ->expects($this->once())
            ->method('isInteractive')
            ->willReturn(true);

        $this->output
            ->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Break'));

        $expectedChoiceQuestion = function (Question $question): bool {
            return (bool) preg_match('#<error>.*?This is a third-party command</error>#i', $question->getQuestion());
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
            ->with($this->equalTo('third-party:command'))
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
}
