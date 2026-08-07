<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Introspection;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\OAuth2\ScopeSet;

final readonly class ArrayTokenIntrospectionMapper
{
    /**
     * @param array<string, mixed> $payload
     */
    public function map(array $payload): TokenIntrospectionResult
    {
        $active = $payload['active'] ?? null;

        if (!is_bool($active)) {
            throw new InvalidArgumentException(
                'Introspection response requires boolean active.'
            );
        }

        if (!$active) {
            return new TokenIntrospectionResult(false);
        }

        $subject = $payload['sub'] ?? null;
        if (!is_string($subject) || $subject === '') {
            throw new InvalidArgumentException(
                'Active introspection response requires sub.'
            );
        }

        $scopeValue = $payload['scope'] ?? '';
        if (!is_string($scopeValue)) {
            throw new InvalidArgumentException(
                'Introspection scope must be a string.'
            );
        }

        $scopeValues = trim($scopeValue) === ''
            ? []
            : (preg_split('/\s+/', trim($scopeValue)) ?: []);

        $expiresAt = $this->timestamp($payload['exp'] ?? null, 'exp');
        $issuedAt = $this->timestamp($payload['iat'] ?? null, 'iat');

        $reserved = ['active', 'sub', 'scope', 'exp', 'iat'];
        $attributes = [];

        foreach ($payload as $name => $value) {
            if (in_array($name, $reserved, true)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $attributes[$name] = $value;
            }
        }

        return new TokenIntrospectionResult(
            true,
            $subject,
            new ScopeSet($scopeValues),
            $expiresAt,
            $issuedAt,
            $attributes
        );
    }

    private function timestamp(
        mixed $value,
        string $field
    ): ?DateTimeImmutable {
        if ($value === null) {
            return null;
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Introspection %s value must be an integer timestamp.',
                    $field
                )
            );
        }

        return (new DateTimeImmutable())->setTimestamp($value);
    }
}
