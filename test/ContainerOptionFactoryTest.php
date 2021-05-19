<?php

declare(strict_types=1);

namespace LaminasTest\Cli;

use Laminas\Cli\ContainerOptionFactory;
use PHPUnit\Framework\TestCase;

/** @psalm-suppress PropertyNotSetInConstructor */
final class ContainerOptionFactoryTest extends TestCase
{
    /** @var ContainerOptionFactory */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ContainerOptionFactory();
    }

    public function testWillCreateInputOptionWithExpectedName(): void
    {
        $option = ($this->factory)();
        self::assertEquals('container', $option->getName());
    }

    public function testWillRequireValueForOption(): void
    {
        $option = ($this->factory)();
        self::assertTrue($option->isValueRequired());
    }

    public function testWontSetShortname(): void
    {
        $option = ($this->factory)();
        self::assertNull($option->getShortcut());
    }
}
