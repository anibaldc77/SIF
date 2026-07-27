<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextRedactionPolicyInterface;
use Sif\Foundation\Exceptions\InvalidContextRedactionPolicyException;

/** Exact, case-sensitive deny-list for context attribute keys. */
final readonly class ContextRedactionPolicy implements ContextRedactionPolicyInterface
{
    /** @var array<string, true> */
    private array $keys;

    /** @param list<string> $attributeKeys */
    public function __construct(array $attributeKeys = [], private string $redactionMarker = '[REDACTED]')
    {
        if ($this->redactionMarker === '') {
            throw new InvalidContextRedactionPolicyException('Context redaction marker must not be empty.');
        }

        $keys = [];

        foreach ($attributeKeys as $attributeKey) {
            if (trim($attributeKey) === '') {
                throw new InvalidContextRedactionPolicyException(
                    'Context redaction attribute keys must be non-empty strings.',
                );
            }

            $keys[$attributeKey] = true;
        }

        $this->keys = $keys;
    }

    public static function none(): self
    {
        return new self();
    }

    public function redacts(string $attributeKey): bool
    {
        return isset($this->keys[$attributeKey]);
    }

    public function marker(): string
    {
        return $this->redactionMarker;
    }
}
