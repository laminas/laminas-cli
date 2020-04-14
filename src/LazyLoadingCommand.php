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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LazyLoadingCommand extends Command
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

        return $command->execute($input, $output);
    }
}
