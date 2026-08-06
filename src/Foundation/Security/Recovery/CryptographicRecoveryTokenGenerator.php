<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use Random\RandomException;
use Sif\Foundation\Security\Contracts\RecoveryTokenGeneratorInterface;
use Sif\Foundation\Security\Exceptions\InvalidRecoveryTokenException;

final readonly class CryptographicRecoveryTokenGenerator implements RecoveryTokenGeneratorInterface
{
    /** @var int<32, 128> */
    private int $entropyBytes;

    public function __construct(int $entropyBytes = 32)
    {
        if ($entropyBytes < 32 || $entropyBytes > 128) {
            throw new InvalidRecoveryTokenException(
                'Recovery token entropy must contain between 32 and 128 random bytes.'
            );
        }

        $this->entropyBytes = $entropyBytes;
    }

    public function generate(): RecoveryToken
    {
        try {
            $bytes = random_bytes($this->entropyBytes);
        } catch (RandomException $exception) {
            throw new InvalidRecoveryTokenException(
                'Cryptographically secure recovery token generation failed.',
                previous: $exception
            );
        }

        return new RecoveryToken(rtrim(strtr(base64_encode($bytes), '+/', '-_'), '='));
    }
}
