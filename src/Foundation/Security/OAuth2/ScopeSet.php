<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

use InvalidArgumentException;

final readonly class ScopeSet
{
    /** @var array<string, string> */
    private array $scopes;

    /**
     * @param iterable<string> $scopes
     */
    public function __construct(iterable $scopes = [])
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $value = trim($scope);

            if (
                $value === ''
                || strlen($value) > 160
                || preg_match('/^[\x21\x23-\x5B\x5D-\x7E]+$/', $value) !== 1
            ) {
                throw new InvalidArgumentException(
                    'OAuth scope must use valid RFC 6749 scope-token characters.'
                );
            }

            $normalized[$value] = $value;
        }

        ksort($normalized);

        $this->scopes = $normalized;
    }

    public function contains(string $scope): bool
    {
        return isset($this->scopes[$scope]);
    }

    public function count(): int
    {
        return count($this->scopes);
    }

    /** @return list<string> */
    public function values(): array
    {
        return array_values($this->scopes);
    }
}
