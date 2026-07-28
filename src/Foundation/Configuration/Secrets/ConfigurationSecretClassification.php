<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets;

enum ConfigurationSecretClassification: string
{
    case Public = 'public';
    case Secret = 'secret';

    public function isSecret(): bool
    {
        return $this === self::Secret;
    }
}
