<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use function sprintf;

final class BoolParam implements InputParamInterface
{
    use InputParamTrait;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getOptionMode(): ?int
    {
        return InputOption::VALUE_NONE;
    }

    public function getQuestion(): Question
    {
        return new ConfirmationQuestion(
            sprintf(
                '<question>%s?</question> [<comment>%s</comment>]',
                $this->description,
                $this->default ? 'Y/n' : 'y/N'
            ),
            $this->default
        );
    }
}
