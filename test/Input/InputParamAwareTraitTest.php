<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
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

    public function invalidOptionModes(): iterable
    {
        yield 'negative'          => [-1];
        yield 'zero'              => [0];
        yield 'out of range'      => [16];
        yield 'multiple none'     => [InputOption::VALUE_NONE | InputOption::VALUE_IS_ARRAY];
        yield 'optional'          => [InputOption::VALUE_OPTIONAL];
        yield 'multiple optional' => [InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY];
        yield 'multiple only'     => [InputOption::VALUE_IS_ARRAY];
    }

    /**
     * @dataProvider invalidOptionModes
     */
    public function testSetOptionModeRaisesExceptionForInvalidModes(int $mode): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option mode');
        $this->inputParam->setOptionMode($mode);
    }

    public function validOptionModes(): iterable
    {
        yield 'none'              => [InputOption::VALUE_NONE];
        yield 'required'          => [InputOption::VALUE_REQUIRED];
        yield 'multiple required' => [InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY];
    }

    /**
     * @dataProvider validOptionModes
     */
    public function testAllowsSettingValidOptionModeCombinations(int $mode): void
    {
        $this->inputParam->setOptionMode($mode);
        $this->assertSame($mode, $this->inputParam->getOptionMode());
    }
}
