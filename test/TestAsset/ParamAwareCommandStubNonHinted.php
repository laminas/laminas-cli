<?php

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @todo Remove once we stop supporting symfony/console v4.
 */
class ParamAwareCommandStubNonHinted extends AbstractParamAwareCommand
{
    /** @var InputInterface */
    public $input;

    /** @var OutputInterface */
    public $output;

    /** @var array */
    public $options = [];

    /** HelperSet */
    private $helperSet;

    public function __construct(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
        parent::__construct('test');
    }

    /**
     * @param string            $name
     * @param string|array|null $shortcut
     * @param null|int          $mode
     * @param string            $description
     * @param null|mixed        $default Defaults to null.
     * @return $this
     */
    public function addOption(
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null
    ) {
        $this->options[$name] = [
            'shortcut'    => $shortcut,
            'mode'        => $mode,
            'description' => $description,
            'default'     => $default,
        ];
        return $this;
    }

    /**
     * @return HelperSet
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input  = $input;
        $this->output = $output;
        return 0;
    }
}
