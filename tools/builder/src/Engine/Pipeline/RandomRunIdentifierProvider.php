<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Pipeline;

use Sif\Builder\Engine\Contract\RunIdentifierProviderInterface;

final class RandomRunIdentifierProvider implements RunIdentifierProviderInterface
{
    public function next(): string
    {
        return 'builder-' . bin2hex(random_bytes(12));
    }
}
