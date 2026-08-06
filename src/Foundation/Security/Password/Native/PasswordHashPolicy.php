<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Native;

use Sif\Foundation\Security\Exceptions\InvalidPasswordHashPolicyException;
use Sif\Foundation\Security\Password\PasswordHashAlgorithm;

final readonly class PasswordHashPolicy
{
    /** @var array<string, int> */
    private array $options;

    /**
     * @param array<string, int> $options
     */
    private function __construct(
        private PasswordHashAlgorithm $identifier,
        private string $nativeAlgorithm,
        array $options
    ) {
        ksort($options, SORT_STRING);
        $this->options = $options;
    }

    public static function runtimeDefault(): self
    {
        return new self(
            new PasswordHashAlgorithm('native.default'),
            PASSWORD_DEFAULT,
            []
        );
    }

    public static function bcrypt(int $cost = 12): self
    {
        if ($cost < 4 || $cost > 31) {
            throw new InvalidPasswordHashPolicyException('Bcrypt cost must be between 4 and 31.');
        }

        return new self(
            new PasswordHashAlgorithm('bcrypt'),
            PASSWORD_BCRYPT,
            ['cost' => $cost]
        );
    }

    public static function argon2id(
        int $memoryCost = 65536,
        int $timeCost = 4,
        int $threads = 1
    ): self {
        if (!defined('PASSWORD_ARGON2ID')) {
            throw new InvalidPasswordHashPolicyException('Argon2id is not available in the current PHP runtime.');
        }

        if ($memoryCost < 8 || $timeCost < 1 || $threads < 1) {
            throw new InvalidPasswordHashPolicyException('Argon2id costs must be positive and within native limits.');
        }

        $algorithm = constant('PASSWORD_ARGON2ID');

        if (!is_string($algorithm)) {
            throw new InvalidPasswordHashPolicyException('Argon2id algorithm identifier is invalid.');
        }

        return new self(
            new PasswordHashAlgorithm('argon2id'),
            $algorithm,
            [
                'memory_cost' => $memoryCost,
                'threads' => $threads,
                'time_cost' => $timeCost,
            ]
        );
    }

    public function identifier(): PasswordHashAlgorithm
    {
        return $this->identifier;
    }

    public function nativeAlgorithm(): string
    {
        return $this->nativeAlgorithm;
    }

    /** @return array<string, int> */
    public function options(): array
    {
        return $this->options;
    }
}
