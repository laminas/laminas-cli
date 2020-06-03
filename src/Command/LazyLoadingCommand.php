<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Command;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * @internal
 */
final class LazyLoadingCommand extends Command
{
    /** @var string */
    private $commandClass;

    /** @var ContainerInterface */
    private $container;

    /** @var null|parent */
    private $command;

    public function __construct(string $name, string $commandClass, ContainerInterface $container)
    {
        parent::__construct();

        $this->commandClass = $commandClass;
        $this->container    = $container;

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

    public function run(InputInterface $input, OutputInterface $output): int
    {
        return $this->getCommand()->run($input, $output);
    }

    // phpcs:ignore WebimpressCodingStandard.Functions.ReturnType.InvalidNoReturn
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new RuntimeException(sprintf('The method %s should never be called.', __METHOD__));
    }

    private function getCommand(): parent
    {
        if ($this->command === null) {
            $this->command = $this->container->get($this->commandClass);
            $this->command->setApplication($this->getApplication());
        }

        return $this->command;
    }

    public function getCommandClass(): string
    {
        return $this->commandClass;
    }
}
