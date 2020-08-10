<?php

/**
 * @see       https://github.com/laminas/laminas-cli for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cli/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cli/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace LaminasTest\Cli\TestAsset\InputMapper;

use Laminas\Cli\Input\Mapper\InputMapperInterface;
use Symfony\Component\Console\Input\InputInterface;

use function ucwords;

class CustomInputMapper implements InputMapperInterface
{
    public function __invoke(InputInterface $input): array
    {
        /** @var string $arg */
        $arg = $input->getArgument('arg');

        /** @var string $opt */
        $opt = $input->getOption('opt');

        return [
            'arg1'   => ucwords($arg . ' ' . $opt),
            '--opt1' => 'my-value',
        ];
    }
}
