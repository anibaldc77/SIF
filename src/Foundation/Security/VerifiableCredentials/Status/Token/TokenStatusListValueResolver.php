<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Token;

use OutOfBoundsException;

final class TokenStatusListValueResolver
{
    public function resolve(
        TokenStatusListDecodedData $data,
        int $index
    ): TokenStatusListValue {
        if (
            $index < 0
            || $index >= $data->entryCount()
        ) {
            throw new OutOfBoundsException(
                'Token Status List index is out of bounds.'
            );
        }

        $bitsPerStatus = $data->bitsPerStatus();
        $bitOffset = $index * $bitsPerStatus;
        $value = 0;

        for ($bit = 0; $bit < $bitsPerStatus; $bit++) {
            $absoluteBit = $bitOffset + $bit;
            $byteIndex = intdiv($absoluteBit, 8);
            $bitIndex = $absoluteBit % 8;
            $byte = ord($data->bytes()[$byteIndex]);
            $bitValue = ($byte >> $bitIndex) & 1;
            $value |= $bitValue << $bit;
        }

        return new TokenStatusListValue(
            $value,
            $bitsPerStatus
        );
    }
}
