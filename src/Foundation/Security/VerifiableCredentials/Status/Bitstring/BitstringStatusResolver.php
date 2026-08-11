<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Bitstring;

use OutOfBoundsException;

final class BitstringStatusResolver
{
    public function resolve(BitstringStatusList $list, int $index): BitstringStatusValue
    {
        if ($index < 0 || $index >= $list->entryCapacity()) {
            throw new OutOfBoundsException('Bitstring status index is outside the list bounds.');
        }

        $startBit = $index * $list->statusSize();
        $value = 0;

        for ($offset = 0; $offset < $list->statusSize(); $offset++) {
            $absoluteBit = $startBit + $offset;
            $byteIndex = intdiv($absoluteBit, 8);
            $bitInByte = $absoluteBit % 8;
            $bit = (ord($list->bitstring()[$byteIndex]) >> (7 - $bitInByte)) & 1;
            $value = ($value << 1) | $bit;
        }

        return new BitstringStatusValue($index, $value, $list->statusSize());
    }
}
