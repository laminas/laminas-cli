<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Cli\Input;

trait InputParamMethodsTrait
{
    /** @var mixed */
    private $default;

    /** @var string */
    private $description = '';

    /**
     * Parameter name; must be set by class composing trait!
     *
     * @var string
     */
    private $name;

    /** @var bool */
    private $required = false;

    /** @var null|string */
    private $shortcut = null;

    /**
     * Default value to use if none provided.
     *
     * @return null|mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefault($defaultValue): self
    {
        $this->default = $defaultValue;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setShortcut(?string $shortcut): self
    {
        $this->shortcut = $shortcut;
        return $this;
    }

    public function setRequiredFlag(bool $required): self
    {
        $this->required = $required;
        return $this;
    }
}
