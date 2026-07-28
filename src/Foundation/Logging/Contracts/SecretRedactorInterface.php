<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

interface SecretRedactorInterface
{
    /**
     * @param array<string, null|bool|int|float|string|array<mixed>> $attributes
     * @return array<string, null|bool|int|float|string|array<mixed>>
     */
    public function redact(array $attributes): array;
}
