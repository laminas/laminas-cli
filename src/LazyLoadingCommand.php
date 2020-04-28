<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_a;
use function sprintf;

/**
 * @internal
 */
final class LazyLoadingCommand extends Command implements CommandListenerInterface
{
    /** @var string */
    private $commandClass;

    /** @var ContainerInterface */
    private $container;

    public function __construct(string $name, string $commandClass, ContainerInterface $container)
    {
        parent::__construct();

        $this->commandClass = $commandClass;
        $this->container = $container;

        /** @var Command $command */
        $command = (new ReflectionClass($commandClass))->newInstanceWithoutConstructor();
        $command->setDefinition(new InputDefinition());
        $command->setName($name);
        $command->configure();

        $this->setName($name);
        $this->setDefinition($command->getDefinition());
        $this->setDescription($command->getDescription());
        $this->setHelp($command->getHelp());
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        /** @var Command $command */
        $command = $this->container->get($this->commandClass);
        $command->setApplication($this->getApplication());

        return $command->execute($input, $output);
    }

    public function getCommandClass() : string
    {
        return $this->commandClass;
    }

    /**
     * @throws RuntimeException
     */
    public function __invoke(ConsoleEvent $event) : int
    {
        if (! is_a($this->commandClass, CommandListenerInterface::class, true)) {
            throw new RuntimeException(sprintf(
                'Command %s cannot be used as listener as it does not implement %s',
                $this->getName(),
                CommandListenerInterface::class
            ));
        }

        /** @var Command&CommandListenerInterface $command */
        $command = $this->container->get($this->commandClass);
        $command->setApplication($this->getApplication());

        return $command($event);
    }
}
