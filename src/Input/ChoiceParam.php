<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Webmozart\Assert\Assert;

use function array_map;
use function implode;
use function is_array;
use function sprintf;

final class ChoiceParam extends AbstractInputParam
{
    use AllowMultipleTrait;

    /** @var array */
    private $haystack;

    /**
     * @param array $haystack Choices to choose from.
     */
    public function __construct(string $name, array $haystack)
    {
        parent::__construct($name);
        $this->haystack = $haystack;
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
     * @param null|string|string[] $defaultValue
     */
    private function createDefaultPrompt($defaultValue): string
    {
        if (null === $defaultValue) {
            return '';
        }

        if (is_array($defaultValue)) {
            Assert::isList($defaultValue);
            Assert::allScalar($defaultValue);
            $defaultValue = implode(', ', array_map(
                /** @param bool|int|float|string $value */
                static function ($value): string {
                    return (string) $value;
                },
                $defaultValue
            ));
        }

        return sprintf(' [<comment>%s</comment>]', $defaultValue);
    }
}
