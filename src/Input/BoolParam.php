<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function get_debug_type;
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
        Assert::boolean($default, sprintf(
            'Default value MUST be a boolean; received "%s"',
            get_debug_type($default)
        ));

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
