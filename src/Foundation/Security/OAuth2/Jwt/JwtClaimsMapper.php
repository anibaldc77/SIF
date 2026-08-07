<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwt;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class JwtClaimsMapper
{
    /**
     * @param array<string, mixed> $claims
     */
    public function map(array $claims): JwtClaims
    {
        $subject = $claims['sub'] ?? null;
        if (!is_string($subject) || $subject === '') {
            throw new InvalidArgumentException(
                'JWT subject claim is required.'
            );
        }

        $issuer = $claims['iss'] ?? null;
        if ($issuer !== null && !is_string($issuer)) {
            throw new InvalidArgumentException('JWT issuer claim is invalid.');
        }

        $audiences = $this->audiences($claims['aud'] ?? []);
        $expiresAt = $this->timestamp($claims['exp'] ?? null, 'exp');
        $issuedAt = $this->timestamp($claims['iat'] ?? null, 'iat');
        $notBefore = $this->timestamp($claims['nbf'] ?? null, 'nbf');

        $scope = $claims['scope'] ?? null;
        if ($scope !== null && !is_string($scope)) {
            throw new InvalidArgumentException('JWT scope claim is invalid.');
        }

        $reserved = ['sub', 'iss', 'aud', 'exp', 'iat', 'nbf', 'scope'];
        $additional = [];

        foreach ($claims as $name => $value) {
            if (in_array($name, $reserved, true)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $additional[$name] = $value;
            }
        }

        return new JwtClaims(
            $subject,
            $issuer,
            $audiences,
            $expiresAt,
            $issuedAt,
            $notBefore,
            $scope,
            $additional
        );
    }

    /**
     * @return list<string>
     */
    private function audiences(mixed $value): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'JWT audience claim is invalid.'
            );
        }

        $result = [];

        foreach ($value as $audience) {
            if (!is_string($audience)) {
                throw new InvalidArgumentException(
                    'JWT audience claim is invalid.'
                );
            }

            $result[] = $audience;
        }

        return array_values(array_unique($result));
    }

    private function timestamp(
        mixed $value,
        string $claim
    ): ?DateTimeImmutable {
        if ($value === null) {
            return null;
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException(
                sprintf('JWT %s claim must be an integer timestamp.', $claim)
            );
        }

        return (new DateTimeImmutable())->setTimestamp($value);
    }
}
