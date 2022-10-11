<?php

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use function array_map;
use function implode;
use function is_array;
use function sprintf;

final class ChoiceParam extends AbstractInputParam
{
    use AllowMultipleTrait;

    /**
     * @param array $haystack Choices to choose from.
     */
    public function __construct(string $name, private array $haystack)
    {
        parent::__construct($name);
    }

    public function getQuestion(): Question
    {
        /** @var null|string|string[] $defaultValue */
        $defaultValue  = $this->getDefault();
        $defaultPrompt = $this->createDefaultPrompt($defaultValue);
        $multiPrompt   = sprintf(
            "\n(Multiple selections allowed; hit Return after each.%s Hit Return to stop prompting)\n",
            $this->isRequired() ? ' At least one selection is required.' : ''
        );

        return new ChoiceQuestion(
            sprintf(
                '<question>%s?</question>%s%s',
                $this->getDescription(),
                $this->getOptionMode() & InputOption::VALUE_IS_ARRAY ? $multiPrompt : '',
                $defaultPrompt
            ),
            $this->haystack,
            $defaultValue
        );
    }

    /**
     * @param null|string|array $defaultValue
     * @psalm-param null|string|scalar[] $defaultValue
     */
    private function createDefaultPrompt($defaultValue): string
    {
        if (null === $defaultValue) {
            return '';
        }

        if (is_array($defaultValue)) {
            $defaultValue = implode(', ', array_map(
                static fn($value): string => (string) $value,
                $defaultValue
            ));
        }

        return sprintf(' [<comment>%s</comment>]', $defaultValue);
    }
}
