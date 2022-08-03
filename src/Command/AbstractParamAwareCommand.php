<?php

declare(strict_types=1);

// phpcs:disable WebimpressCodingStandard.Commenting.TagWithType.InvalidTypeFormat

namespace Laminas\Cli\Command;

use Laminas\Cli\Input\InputParamInterface;
use Laminas\Cli\Input\ParamAwareInput;
use Laminas\Cli\Input\ParamAwareInputInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * Provide promptable input options to your command.
 *
 * Extend this class to create a symfony/console command that provides the
 * ability to define options that will result in interactive prompts when not
 * provided. Such parameters can be added using the `addParam()` construct. When
 * present, you can then use `$input->getParam($name)` to retrieve the value. If
 * the value was provided as an option, that value will be returned; otherwise,
 * it will prompt the user for the value.
 */
abstract class AbstractParamAwareCommand extends Command
{
    /** @var array<string, InputParamInterface> */
    private array $inputParams = [];

    /**
     * @return $this
     * @throws RuntimeException
     */
    final public function addParam(InputParamInterface $param): self
    {
        $name = $param->getName();

        $this->addOption(
            $name,
            $param->getShortcut(),
            $param->getOptionMode(),
            $param->getDescription()
            // default null, on purpose
        );

        $this->inputParams[$name] = $param;

        return $this;
    }

    /**
     * Overrides the Symfony\Component\Console\Command\Command::run method to
     * decorate incoming input via a ParamAwareInputInterface implementation.
     *
     * If you override the method in your own code, you MUST call
     * `parent::run()` OR inline the code from this implementation if you are
     * using input parameters.
     *
     * {@inheritDoc}
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        return parent::run(
            $this->decorateInputToBeParamAware($input, $output),
            $output
        );
    }

    /**
     * Decorate an input to be parameter aware.
     *
     * This method decorates incoming input such that it can fulfill the
     * ParamAwareInputInterface. If it already is, it will be returned verbatim;
     * otherwise, it is decorated in an instance appropriate to the
     * symfony/console version currently in use.
     */
    final protected function decorateInputToBeParamAware(
        InputInterface $input,
        OutputInterface $output
    ): ParamAwareInputInterface {
        if ($input instanceof ParamAwareInputInterface) {
            return $input;
        }

        $helperSet = $this->getHelperSet();
        Assert::isInstanceOf($helperSet, HelperSet::class);

        $questionHelper = $helperSet->get('question');
        Assert::isInstanceOf($questionHelper, QuestionHelper::class);
        return new ParamAwareInput(
            $input,
            $output,
            $questionHelper,
            $this->inputParams
        );
    }
}
