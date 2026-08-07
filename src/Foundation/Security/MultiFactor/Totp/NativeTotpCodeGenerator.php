<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use DateTimeImmutable;
use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final readonly class NativeTotpCodeGenerator
{
    public function __construct(private Base32Codec $codec = new Base32Codec())
    {
    }

    public function generate(
        TotpSecret $secret,
        TotpParameters $parameters,
        DateTimeImmutable $at
    ): TotpCode {
        $timestamp = $at->getTimestamp();

        if ($timestamp < 0) {
            throw new InvalidTotpConfigurationException('TOTP timestamps before the Unix epoch are not supported.');
        }

        return $this->generateForCounter(
            $secret,
            $parameters,
            intdiv($timestamp, $parameters->periodSeconds())
        );
    }

    public function generateForCounter(
        TotpSecret $secret,
        TotpParameters $parameters,
        int $counter
    ): TotpCode {
        if ($counter < 0) {
            throw new InvalidTotpConfigurationException('TOTP counter cannot be negative.');
        }

        return $secret->expose(function (string $encodedSecret) use ($parameters, $counter): TotpCode {
            $binarySecret = $this->codec->decode($encodedSecret);
            $counterBytes = pack('N2', intdiv($counter, 4294967296), $counter % 4294967296);
            $digest = hash_hmac($parameters->algorithm()->value(), $counterBytes, $binarySecret, true);

            if (strlen($digest) < 20) {
                throw new InvalidTotpConfigurationException('Unable to calculate the TOTP HMAC digest.');
            }

            $offset = ord($digest[strlen($digest) - 1]) & 15;
            $binaryCode = (
                ((ord($digest[$offset]) & 127) << 24)
                | ((ord($digest[$offset + 1]) & 255) << 16)
                | ((ord($digest[$offset + 2]) & 255) << 8)
                | (ord($digest[$offset + 3]) & 255)
            );

            $modulus = 10 ** $parameters->digits();
            $value = str_pad((string) ($binaryCode % $modulus), $parameters->digits(), '0', STR_PAD_LEFT);

            return new TotpCode($value);
        });
    }
}
