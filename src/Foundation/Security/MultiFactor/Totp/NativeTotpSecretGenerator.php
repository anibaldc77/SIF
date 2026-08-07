<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Random\RandomException;
use Sif\Foundation\Security\Contracts\TotpSecretGeneratorInterface;
use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final readonly class NativeTotpSecretGenerator implements TotpSecretGeneratorInterface
{
    public function __construct(
        private Base32Codec $codec = new Base32Codec(),
        private int $entropyBytes = 20
    ) {
        if ($entropyBytes < 20 || $entropyBytes > 128) {
            throw new InvalidTotpConfigurationException('TOTP entropy must be between 20 and 128 bytes.');
        }
    }

    public function generate(): TotpSecret
    {
        $entropyBytes = $this->entropyBytes;
        assert($entropyBytes > 0);

        try {
            $bytes = random_bytes($entropyBytes);
        } catch (RandomException $exception) {
            throw new InvalidTotpConfigurationException('Unable to generate cryptographically secure TOTP entropy.', 0, $exception);
        }

        return new TotpSecret($this->codec->encode($bytes));
    }
}
