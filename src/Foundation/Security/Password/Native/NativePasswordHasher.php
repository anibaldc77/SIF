<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Native;

use Sif\Foundation\Security\Contracts\PasswordHasherInterface;
use Sif\Foundation\Security\Exceptions\PasswordHashingException;
use Sif\Foundation\Security\Password\PasswordHashAlgorithm;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final readonly class NativePasswordHasher implements PasswordHasherInterface
{
    public function __construct(private PasswordHashPolicy $policy)
    {
    }

    public function hash(PasswordSecret $secret): StoredPasswordHash
    {
        $encodedHash = $secret->expose(fn (string $value): string => $this->encode($value));
        $information = password_get_info($encodedHash);
        $algorithmName = $information['algoName'];

        if ($algorithmName === 'unknown') {
            throw new PasswordHashingException('The native password hash could not be identified.');
        }

        return new StoredPasswordHash(
            new PasswordHashAlgorithm($algorithmName),
            $encodedHash,
            $this->normalizeOptions($information['options'])
        );
    }

    private function encode(#[\SensitiveParameter] string $value): string
    {
        return password_hash(
            $value,
            $this->policy->nativeAlgorithm(),
            $this->policy->options()
        );
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, bool|int|string>
     */
    private function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $name => $value) {
            if (is_bool($value) || is_int($value) || is_string($value)) {
                $normalized[$name] = $value;
            }
        }

        return $normalized;
    }
}
