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
    public function __invoke(InputInterface $input) : array
    {
        return [
            'arg1' => ucwords($input->getArgument('arg') . ' ' . $input->getOption('opt')),
            '--opt1' => 'my-value',
        ];
    }
}
