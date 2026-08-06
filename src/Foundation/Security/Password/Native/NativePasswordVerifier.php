<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Native;

use Sif\Foundation\Security\Contracts\PasswordVerifierInterface;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\PasswordVerificationResult;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final readonly class NativePasswordVerifier implements PasswordVerifierInterface
{
    public function __construct(private PasswordHashPolicy $policy)
    {
    }

    public function verify(
        PasswordCredential $credential,
        StoredPasswordHash $storedHash
    ): PasswordVerificationResult {
        return $storedHash->exposeEncodedHash(
            function (string $encodedHash) use ($credential): PasswordVerificationResult {
                if (password_get_info($encodedHash)['algoName'] === 'unknown') {
                    return PasswordVerificationResult::rejected();
                }

                $verified = $credential->secret()->expose(
                    static fn (string $secret): bool => password_verify($secret, $encodedHash)
                );

                if (!$verified) {
                    return PasswordVerificationResult::rejected();
                }

                return PasswordVerificationResult::verified(
                    password_needs_rehash(
                        $encodedHash,
                        $this->policy->nativeAlgorithm(),
                        $this->policy->options()
                    )
                );
            }
        );
    }
}
