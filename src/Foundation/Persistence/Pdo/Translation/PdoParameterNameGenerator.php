<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Translation;

final class PdoParameterNameGenerator
{
    private int $sequence = 0;

    public function next(string $field): string
    {
        $normalized = strtolower((string) preg_replace('/[^A-Za-z0-9_]+/', '_', $field));
        $normalized = trim($normalized, '_');
        if ($normalized === '' || ctype_digit($normalized[0])) {
            $normalized = 'value_' . $normalized;
        }

        return sprintf('p_%s_%d', $normalized, $this->sequence++);
    }
}
