<?php

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ApplicationFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

/** @psalm-suppress PropertyNotSetInConstructor */
class ApplicationFactoryTest extends TestCase
{
    public function testPullsEventDispatcherFromContainerWhenPresent(): void
    {
        $this->assertInstanceOf(Application::class, (new ApplicationFactory())());
    }

    public function testApplicationDefinitionContainsContainerOptionSoItIsAvailableForEveryCommand(): void
    {
        $application = (new ApplicationFactory())();
        $definition  = $application->getDefinition();
        self::assertTrue($definition->hasOption(ApplicationFactory::CONTAINER_OPTION));
    }
}
