<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

use InvalidArgumentException;
use Laminas\Cli\Validator\IntValidator;
use Laminas\Cli\Validator\PathValidator;
use Laminas\Cli\Validator\StringValidator;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use function array_map;
use function in_array;
use function is_array;
use function is_dir;
use function is_numeric;
use function is_object;
use function preg_replace;
use function scandir;

use const PHP_EOL;

final class InputParam
{
    public const TYPE_BOOL = 'bool';
    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string'; // string, preg
    public const TYPE_PATH = 'path';  // dir or file
    public const TYPE_CHOICE = 'choice'; // in array
    public const TYPE_CUSTOM = 'custom'; // question must be provided in options

    private const ALLOWED_TYPES = [
        self::TYPE_BOOL,
        self::TYPE_INT,
        self::TYPE_STRING,
        self::TYPE_PATH,
        self::TYPE_CHOICE,
        self::TYPE_CUSTOM,
    ];

    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $type;

    /** @var bool */
    private $required;

    /** @var null|mixed */
    private $default;

    /** @var array<string, mixed> */
    private $options;

    /**
     * @internal
     *
     * @param null|mixed $default
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $name,
        string $description,
        string $type,
        bool $required = false,
        $default = null,
        array $options = []
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->setType($type);
        $this->required = $required;
        $this->default = $default;
        $this->setOptions($options);
    }

    /**
     * @internal
     * @throws InvalidArgumentException
     */
    private function setType(string $type) : void
    {
        if (! in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException('Invalid input param type provided: ' . $type);
        }

        $this->type = $type;
    }

    /**
     * @internal
     *
     * @param array<string, mixed> $options
     * @throws InvalidArgumentException
     */
    private function setOptions(array $options) : void
    {
        if ($this->type === self::TYPE_CUSTOM
            && (! isset($options['question'])
                || ! is_object($options['question'])
                || $options['question'] instanceof Question)
        ) {
            throw new InvalidArgumentException('Question must be provided with custom input param');
        }

        if ($this->type === self::TYPE_CHOICE
            && (! isset($options['haystack'])
                || ! is_array($options['haystack']))
        ) {
            throw new InvalidArgumentException('Missing haystack for choice input param');
        }

        $this->options = $options;
    }

    /**
     * @internal
     */
    public function getQuestion(string $name) : Question
    {
        if ($this->type === self::TYPE_CUSTOM) {
            return $this->options['question'];
        }

        $this->options['required'] = $this->required;

        if ($this->type === self::TYPE_BOOL) {
            return new ConfirmationQuestion(
                '<question>' . $this->description . '?</question>'
                . ' [<comment>' . ($this->default ? 'Y/n' : 'y/N') . '</comment>]',
                $this->default
            );
        }

        if ($this->type === self::TYPE_CHOICE) {
            return new ChoiceQuestion(
                '<question>' . $this->description . ':</question>'
                . ($this->default !== null
                    ? ' [<comment>' . $this->default . '</comment>]'
                    : ''),
                $this->options['haystack'],
                $this->default
            );
        }

        $question = new Question(
            '<question>' . $this->description . ':</question>'
            . ($this->default !== null
                ? ' [<comment>' . $this->default . '</comment>]'
                : '')
            . PHP_EOL . ' > ',
            $this->default
        );

        $validator = null;
        switch ($this->type) {
            case self::TYPE_INT:
                $validator = new IntValidator($this->options);
                $question->setNormalizer(static function ($value) {
                    if (is_numeric($value) && (string) (int) $value === $value) {
                        return (int) $value;
                    }

                    return $value;
                });
                break;
            case self::TYPE_STRING:
                $validator = new StringValidator($this->options);
                break;
            case self::TYPE_PATH:
                $validator = new PathValidator($this->options);
                // @todo use base and type (file / dir only)
                $question->setAutocompleterCallback(static function (string $userInput) : array {
                    // Strip any characters from the last slash to the end of the string
                    // to keep only the last directory and generate suggestions for it
                    $inputPath = preg_replace('%(/|^)[^/]*$%', '$1', $userInput);
                    $inputPath = $inputPath === '' ? '.' : $inputPath;

                    // CAUTION - this example code allows unrestricted access to the
                    // entire filesystem. In real applications, restrict the directories
                    // where files and dirs can be found
                    $foundFilesAndDirs = is_dir($inputPath) ? scandir($inputPath) : [];

                    return array_map(static function (string $dirOrFile) use ($inputPath) : string {
                        return $inputPath . $dirOrFile;
                    }, $foundFilesAndDirs);
                });
                break;
        }

        if ($validator) {
            $question->setValidator($validator);
        }

        return $question;
    }

    /**
     * @internal
     * @return null|mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @internal
     */
    public function isRequired() : bool
    {
        return $this->required;
    }
}
