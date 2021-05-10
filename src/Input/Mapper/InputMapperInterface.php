<?php

declare(strict_types=1);

namespace Laminas\Cli\Input\Mapper;

use Symfony\Component\Console\Input\InputInterface;

interface InputMapperInterface
{
    public function __invoke(InputInterface $input): array;
}
