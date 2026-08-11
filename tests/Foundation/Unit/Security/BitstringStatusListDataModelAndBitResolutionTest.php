<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Status\Bitstring\BitstringStatusList;
use Sif\Foundation\Security\VerifiableCredentials\Status\Bitstring\BitstringStatusResolver;
use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusPurpose;

final class BitstringStatusListDataModelAndBitResolutionTest extends TestCase
{
    public function testListKeepsUriPurposeStatusSizeAndCapacityExplicit(): void
    {
        $list = $this->list(str_repeat("\0", 16384));

        self::assertSame('https://issuer.example.test/status/1', $list->listUri());
        self::assertSame(CredentialStatusPurpose::Revocation, $list->purpose());
        self::assertSame(1, $list->statusSize());
        self::assertSame(131072, $list->bitLength());
        self::assertSame(131072, $list->entryCapacity());
    }

    public function testResolverUsesLeftMostBitAsIndexZero(): void
    {
        $bytes = str_repeat("\0", 16384);
        $bytes[0] = chr(0b10000001);
        $resolver = new BitstringStatusResolver();
        $list = $this->list($bytes);

        self::assertTrue($resolver->resolve($list, 0)->asserted());
        self::assertFalse($resolver->resolve($list, 1)->asserted());
        self::assertTrue($resolver->resolve($list, 7)->asserted());
        self::assertFalse($resolver->resolve($list, 8)->asserted());
    }

    public function testResolverReadsMultiBitEntriesDeterministically(): void
    {
        $bytes = str_repeat("\0", 16384);
        $bytes[0] = chr(0b01101100);
        $list = new BitstringStatusList(
            'https://issuer.example.test/status/2',
            CredentialStatusPurpose::Suspension,
            $bytes,
            2
        );
        $resolver = new BitstringStatusResolver();

        self::assertSame(1, $resolver->resolve($list, 0)->value());
        self::assertSame(2, $resolver->resolve($list, 1)->value());
        self::assertSame(3, $resolver->resolve($list, 2)->value());
        self::assertSame(0, $resolver->resolve($list, 3)->value());
    }

    public function testResolverCanCrossByteBoundary(): void
    {
        $bytes = str_repeat("\0", 16384);
        $bytes[0] = chr(0b00000001);
        $bytes[1] = chr(0b10000000);
        $list = new BitstringStatusList(
            'https://issuer.example.test/status/3',
            CredentialStatusPurpose::Revocation,
            $bytes,
            3
        );

        self::assertSame(3, (new BitstringStatusResolver())->resolve($list, 2)->value());
    }

    public function testListRejectsUndersizedBitstrings(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->list(str_repeat("\0", 16383));
    }

    public function testResolverRejectsIndexesOutsideCapacity(): void
    {
        $list = $this->list(str_repeat("\0", 16384));

        $this->expectException(OutOfBoundsException::class);
        (new BitstringStatusResolver())->resolve($list, $list->entryCapacity());
    }

    public function testBitResolutionRemainsTransportCompressionAndStorageNeutral(): void
    {
        foreach ([BitstringStatusList::class, BitstringStatusResolver::class] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('gzdecode', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
        }
    }

    private function list(string $bytes): BitstringStatusList
    {
        return new BitstringStatusList(
            'https://issuer.example.test/status/1',
            CredentialStatusPurpose::Revocation,
            $bytes
        );
    }
}
