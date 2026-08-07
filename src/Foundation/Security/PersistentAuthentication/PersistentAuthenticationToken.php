<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

final readonly class PersistentAuthenticationToken
{
    public function __construct(
        private PersistentAuthenticationSelector $selector,
        private PersistentAuthenticationValidator $validator
    ) {
    }

    public function selector(): PersistentAuthenticationSelector
    {
        return $this->selector;
    }

    public function validator(): PersistentAuthenticationValidator
    {
        return $this->validator;
    }

    public function validatorDigest(): PersistentAuthenticationValidatorDigest
    {
        return $this->validator->digest();
    }

    /** @return array{selector_fingerprint:string,validator:string} */
    public function __debugInfo(): array
    {
        return [
            'selector_fingerprint' => hash(
                'sha256',
                $this->selector->value()
            ),
            'validator' => '[REDACTED]',
        ];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException(
            'Persistent authentication tokens cannot be serialized.'
        );
    }
}
