<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use Sif\Foundation\Security\Contracts\AuthenticatorInterface;
use Sif\Foundation\Security\Credentials\CredentialType;
use Sif\Foundation\Security\Exceptions\AmbiguousCredentialTypeException;
use Sif\Foundation\Security\Exceptions\DuplicateAuthenticatorException;
use Sif\Foundation\Security\Exceptions\InvalidAuthenticatorException;

final class AuthenticatorRegistry
{
    /** @var array<string, AuthenticatorInterface> */
    private array $authenticatorsById = [];

    /** @var array<string, AuthenticatorInterface> */
    private array $authenticatorsByCredentialType = [];

    public function register(AuthenticatorInterface $authenticator): void
    {
        $id = $authenticator->id()->value();

        if (isset($this->authenticatorsById[$id])) {
            throw new DuplicateAuthenticatorException(sprintf('Authenticator "%s" is already registered.', $id));
        }

        $types = $authenticator->supportedCredentialTypes();

        if ($types === []) {
            throw new InvalidAuthenticatorException(sprintf('Authenticator "%s" must support at least one credential type.', $id));
        }

        $normalizedTypes = [];

        foreach ($types as $type) {
            $value = $type->value();

            if (isset($normalizedTypes[$value])) {
                throw new InvalidAuthenticatorException(
                    sprintf('Authenticator "%s" declares credential type "%s" more than once.', $id, $value)
                );
            }

            if (isset($this->authenticatorsByCredentialType[$value])) {
                throw new AmbiguousCredentialTypeException(
                    sprintf('Credential type "%s" is already assigned to another authenticator.', $value)
                );
            }

            $normalizedTypes[$value] = $type;
        }

        $this->authenticatorsById[$id] = $authenticator;

        foreach ($normalizedTypes as $value => $type) {
            $this->authenticatorsByCredentialType[$value] = $authenticator;
        }
    }

    public function findFor(CredentialType $type): ?AuthenticatorInterface
    {
        return $this->authenticatorsByCredentialType[$type->value()] ?? null;
    }

    /** @return list<AuthenticatorInterface> */
    public function all(): array
    {
        return array_values($this->authenticatorsById);
    }
}
