<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use Laminas\Cli\Input\InputParamInterface;
use Laminas\Cli\Input\InputParamTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

class InputParamAwareTraitTest extends TestCase
{
    /** @var object */
    private $inputParam;

    public function setUp(): void
    {
        $this->inputParam = new class implements InputParamInterface {
            use InputParamTrait;

            public function __construct()
            {
                $this->name = 'test';
            }

            public function getOptionMode(): int
            {
                return InputOption::VALUE_NONE;
            }

            public function getQuestion(): Question
            {
                return new Question('this will not be asked');
            }
        };
    }

    public function testCanRetrieveName(): void
    {
        $this->assertSame('test', $this->inputParam->getName());
    }

    public function testDefaultValueIsNullByDefault(): void
    {
        $this->assertNull($this->inputParam->getDefault());
    }

    public function testCanSetDefaultValue(): void
    {
        $this->inputParam->setDefault('test');
        $this->assertSame('test', $this->inputParam->getDefault());
    }

    public function testDescriptionIsEmptyByDefault(): void
    {
        $this->assertSame('', $this->inputParam->getDescription());
    }

    public function testCanSetDescription(): void
    {
        $this->inputParam->setDescription('test');
        $this->assertSame('test', $this->inputParam->getDescription());
    }

    public function testShortcutIsNullByDefault(): void
    {
        $this->assertNull($this->inputParam->getShortcut());
    }

    public function testCanSetShortcut(): void
    {
        $this->inputParam->setShortcut('t');
        $this->assertSame('t', $this->inputParam->getShortcut());
    }

    public function testIsNotRequiredByDefault(): void
    {
        $this->assertFalse($this->inputParam->isRequired());
    }

    public function testCanSetRequiredFlag(): void
    {
        $this->inputParam->setRequiredFlag(true);
        $this->assertTrue($this->inputParam->isRequired());
    }
}
