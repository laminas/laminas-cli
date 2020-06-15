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

final class BoolParam extends AbstractInputParam
{
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->optionMode = InputOption::VALUE_NONE;
    }

    public function getQuestion(): Question
    {
        $default = $this->getDefault();
        return new ConfirmationQuestion(
            sprintf(
                '<question>%s?</question> [<comment>%s</comment>]',
                $this->getDescription(),
                $default ? 'Y/n' : 'y/N'
            ),
            $default
        );
    }
}
