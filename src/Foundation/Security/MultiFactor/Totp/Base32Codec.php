<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final class Base32Codec
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function encode(#[\SensitiveParameter] string $binary): string
    {
        if ($binary === '') {
            throw new InvalidTotpConfigurationException('Base32 source bytes cannot be empty.');
        }

        $buffer = 0;
        $bits = 0;
        $encoded = '';
        $length = strlen($binary);

        for ($index = 0; $index < $length; $index++) {
            $buffer = ($buffer << 8) | ord($binary[$index]);
            $bits += 8;

            while ($bits >= 5) {
                $bits -= 5;
                $encoded .= self::ALPHABET[($buffer >> $bits) & 31];
            }
        }

        if ($bits > 0) {
            $encoded .= self::ALPHABET[($buffer << (5 - $bits)) & 31];
        }

        return $encoded;
    }

    public function decode(#[\SensitiveParameter] string $encoded): string
    {
        $normalized = strtoupper(str_replace([' ', '-', '='], '', trim($encoded)));

        if ($normalized === '' || preg_match('/^[A-Z2-7]+$/', $normalized) !== 1) {
            throw new InvalidTotpConfigurationException('Base32 input must contain unpadded Base32 characters.');
        }

        $buffer = 0;
        $bits = 0;
        $decoded = '';
        $length = strlen($normalized);

        for ($index = 0; $index < $length; $index++) {
            $value = strpos(self::ALPHABET, $normalized[$index]);

            if ($value === false) {
                throw new InvalidTotpConfigurationException('Base32 input contains an invalid character.');
            }

            $buffer = ($buffer << 5) | $value;
            $bits += 5;

            if ($bits >= 8) {
                $bits -= 8;
                $decoded .= chr(($buffer >> $bits) & 255);
            }
        }

        if ($decoded === '') {
            throw new InvalidTotpConfigurationException('Base32 input did not produce secret bytes.');
        }

        return $decoded;
    }
}
