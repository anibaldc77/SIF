<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Contract;

interface ComponentCatalogInterface
{
    /** @return list<string> */
    public function analyzers(): array;

    /** @return list<string> */
    public function generators(): array;

    /** @return list<string> */
    public function reporters(): array;
}
