<?php

declare(strict_types=1);

// phpcs:disable WebimpressCodingStandard.Functions.ReturnType

namespace Laminas\Cli\Input;

use Symfony\Component\Console\Question\Question;

interface InputParamInterface
{
    /**
     * Default value to use if none provided.
     *
     * @return mixed
     */
    public function getDefault();

    public function getDescription(): string;

    public function getName(): string;

    /**
     * Return the InputOption VALUE_* type.
     */
    public function getOptionMode(): int;

    /**
     * @return null|string|string[]
     */
    public function getShortcut();

    public function getQuestion(): Question;

    public function isRequired(): bool;

    /**
     * @param mixed $defaultValue
     * @return $this
     */
    public function setDefault($defaultValue): InputParamInterface;

    /**
     * @return $this
     */
    public function setDescription(string $description): InputParamInterface;

    /**
     * @param null|string|string[] $shortcut One of (a) a string with a single
     *     shortcut, (b) a string with multiple shortcuts separated by a "|"
     *     character, or (c) an array of shortcuts.
     * @return $this
     */
    public function setShortcut($shortcut): InputParamInterface;

    /**
     * @return $this
     */
    public function setRequiredFlag(bool $required): InputParamInterface;
}
