<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Input\AbstractInputParam;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;

class AbstractInputParamTest extends TestCase
{
    /** @var AbstractInputParam */
    private $param;

    public function setUp(): void
    {
        $this->param = new class ('test') extends AbstractInputParam {
            // phpcs:ignore WebimpressCodingStandard.Functions.ReturnType.InvalidNoReturn
            public function getQuestion(): Question
            {
                throw new RuntimeException('getQuestion should not be called');
            }
        };
    }

    public function testDescriptionIsEmptyByDefault(): void
    {
        $this->assertSame('', $this->param->getDescription());
    }

    public function testCanSetAndRetrieveDescription(): void
    {
        $description = 'This is the description';
        $this->param->setDescription($description);
        $this->assertSame($description, $this->param->getDescription());
    }

    public function testDefaultValueIsNullByDefault(): void
    {
        $this->assertNull($this->param->getDefault());
    }

    public function testCanSetAndRetrieveDefaultValue(): void
    {
        $default = 'This is the default value';
        $this->param->setDefault($default);
        $this->assertSame($default, $this->param->getDefault());
    }

    public function testCanRetrieveName(): void
    {
        $this->assertSame('test', $this->param->getName());
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
        $this->param->setOptionMode($mode);
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
        $this->param->setOptionMode($mode);
        $this->assertSame($mode, $this->param->getOptionMode());
    }

    public function testNotRequiredByDefault(): void
    {
        $this->assertFalse($this->param->isRequired());
    }

    public function testCanSetRequiredFlag(): void
    {
        $this->param->setRequiredFlag(true);
        $this->assertTrue($this->param->isRequired());
    }

    public function testShortcutIsNullByDefault(): void
    {
        $this->assertNull($this->param->getShortcut());
    }

    public function invalidShortcutValues(): iterable
    {
        yield 'bool'               => [true];
        yield 'int'                => [1];
        yield 'float'              => [1.1];
        yield 'object'             => [(object) ['foo' => 'bar']];
        yield 'array with boolean' => [[true], 'Only string values'];
        yield 'array with int'     => [[1], 'Only string values'];
        yield 'array with float'   => [[1.1], 'Only string values'];
        yield 'array with object'  => [[(object) ['foo' => 'bar']], 'Only string values'];
    }

    /**
     * @dataProvider invalidShortcutValues
     * @param mixed $shortcut
     */
    public function testSettingShortcutShouldRaiseExceptionForInvalidValues(
        $shortcut,
        string $expectedMesage = 'must be null, a string, or an array'
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMesage);
        $this->param->setShortcut($shortcut);
    }

    public function validShortcutValues(): iterable
    {
        yield 'null'                    => [null];
        yield 'string'                  => ['s'];
        yield 'multi-string'            => ['s|x'];
        yield 'array with string'       => [['s']];
        yield 'array with multi-string' => [['s|x']];
    }

    /**
     * @dataProvider validShortcutValues
     * @param mixed $shortcut
     */
    public function testAllowsSettingShortcutWithValidValues($shortcut): void
    {
        $this->param->setShortcut($shortcut);
        $this->assertSame($shortcut, $this->param->getShortcut());
    }
}
