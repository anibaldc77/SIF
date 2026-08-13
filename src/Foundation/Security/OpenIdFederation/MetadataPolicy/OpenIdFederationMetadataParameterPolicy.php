<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\MetadataPolicy;

use InvalidArgumentException;

final readonly class OpenIdFederationMetadataParameterPolicy
{
    /**
     * @param array<string, mixed> $operators
     */
    public function __construct(
        private array $operators
    ) {
        if ($this->operators === []) {
            throw new InvalidArgumentException(
                'OpenID Federation metadata parameter policy cannot be empty.'
            );
        }

        foreach (array_keys($this->operators) as $operator) {
            if (OpenIdFederationMetadataPolicyOperator::tryFrom($operator) === null) {
                throw new InvalidArgumentException(
                    'Unsupported standard OpenID Federation metadata policy operator.'
                );
            }
        }
    }

    /** @return array<string, mixed> */
    public function operators(): array
    {
        return $this->operators;
    }

    public function has(OpenIdFederationMetadataPolicyOperator $operator): bool
    {
        return array_key_exists($operator->value, $this->operators);
    }

    public function value(OpenIdFederationMetadataPolicyOperator $operator): mixed
    {
        return $this->operators[$operator->value] ?? null;
    }
}
