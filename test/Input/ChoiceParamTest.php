<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\Input;

use Laminas\Cli\Input\ChoiceParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ChoiceParamTest extends TestCase
{
    /** @var string[] */
    private $choices;

    /** @var ChoiceParam */
    private $param;

    public function setUp(): void
    {
        $this->choices = [
            'Red',
            'Green',
            'Blue',
        ];

        $this->param = new ChoiceParam(
            'test',
            $this->choices
        );
        $this->param->setDescription('Which color');
    }

    public function testUsesValueRequiredOptionMode(): void
    {
        $this->assertSame(InputOption::VALUE_REQUIRED, $this->param->getOptionMode());
    }

    public function defaultChoices(): iterable
    {
        $question = '<question>Which color?</question>';

        yield 'no default' => [null, $question];
        yield 'Red'        => ['Red', $question . ' [<comment>Red</comment>]'];
        yield 'Blue'       => ['Blue', $question . ' [<comment>Blue</comment>]'];
        yield 'Green'      => ['Green', $question . ' [<comment>Green</comment>]'];
    }

    /**
     * @dataProvider defaultChoices
     */
    public function testQuestionReturnedIncludesChoicesAndDefault(
        ?string $default,
        string $expectedQuestionText
    ): void {
        $this->param->setDefault($default);
        /** @var ChoiceQuestion $question */
        $question = $this->param->getQuestion();
        $this->assertInstanceOf(ChoiceQuestion::class, $question);
        $this->assertEquals($expectedQuestionText, $question->getQuestion());
        $this->assertSame($this->choices, $question->getChoices());
    }
}
