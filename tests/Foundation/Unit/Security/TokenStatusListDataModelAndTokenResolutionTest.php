<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\TokenStatusListAuthenticityVerifierInterface;
use Sif\Foundation\Security\Contracts\TokenStatusListDecoderInterface;
use Sif\Foundation\Security\Contracts\TokenStatusListValuePolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusList;
use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusListDecodedData;
use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusListReference;
use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusListValue;
use Sif\Foundation\Security\VerifiableCredentials\Status\Token\TokenStatusListValueResolver;

final class TokenStatusListDataModelAndTokenResolutionTest extends TestCase
{
    public function testReferenceKeepsUriAndIndexExplicit(): void
    {
        $reference = new TokenStatusListReference(
            'https://issuer.example.test/status/1',
            42
        );

        self::assertSame(
            'https://issuer.example.test/status/1',
            $reference->uri()
        );
        self::assertSame(42, $reference->index());
    }

    public function testStatusListKeepsEncodedListBitsTtlAndAggregationExplicit(): void
    {
        $list = new TokenStatusList(
            'encoded-status-list',
            2,
            300,
            'https://issuer.example.test/status-aggregation'
        );

        self::assertSame(
            'encoded-status-list',
            $list->encodedList()
        );
        self::assertSame(2, $list->bitsPerStatus());
        self::assertSame(300, $list->timeToLive());
        self::assertSame(
            'https://issuer.example.test/status-aggregation',
            $list->aggregationUri()
        );
    }

    public function testDecodedDataComputesEntryCount(): void
    {
        $data = new TokenStatusListDecodedData(
            "\x00\x00",
            2
        );

        self::assertSame(8, $data->entryCount());
    }

    public function testResolverSupportsOneBitStatusValues(): void
    {
        $data = new TokenStatusListDecodedData(
            chr(0b00000101),
            1
        );
        $resolver = new TokenStatusListValueResolver();

        self::assertSame(1, $resolver->resolve($data, 0)->value());
        self::assertSame(0, $resolver->resolve($data, 1)->value());
        self::assertSame(1, $resolver->resolve($data, 2)->value());
    }

    public function testResolverSupportsMultiBitStatusValues(): void
    {
        $data = new TokenStatusListDecodedData(
            chr(0b11100100),
            2
        );
        $resolver = new TokenStatusListValueResolver();

        self::assertSame(0, $resolver->resolve($data, 0)->value());
        self::assertSame(1, $resolver->resolve($data, 1)->value());
        self::assertSame(2, $resolver->resolve($data, 2)->value());
        self::assertSame(3, $resolver->resolve($data, 3)->value());
    }

    public function testResolverRejectsOutOfBoundsIndex(): void
    {
        $data = new TokenStatusListDecodedData(
            chr(0),
            2
        );

        $this->expectException(OutOfBoundsException::class);

        (new TokenStatusListValueResolver())->resolve(
            $data,
            4
        );
    }

    public function testValueEnforcesRangeForConfiguredBitWidth(): void
    {
        $value = new TokenStatusListValue(
            3,
            2
        );

        self::assertSame(3, $value->value());
        self::assertSame(2, $value->bitsPerStatus());
    }

    public function testTokenStatusListContractsAreTypedAndSeparated(): void
    {
        $decoder = new \ReflectionMethod(
            TokenStatusListDecoderInterface::class,
            'decode'
        );

        self::assertSame(
            TokenStatusListDecodedData::class,
            (string) $decoder->getReturnType()
        );

        foreach ([
            TokenStatusListAuthenticityVerifierInterface::class,
            TokenStatusListValuePolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testTokenStatusListLayerRemainsJoseCoseCompressionAndTransportNeutral(): void
    {
        foreach ([
            TokenStatusListDecoderInterface::class,
            TokenStatusListAuthenticityVerifierInterface::class,
            TokenStatusListValuePolicyInterface::class,
            TokenStatusListReference::class,
            TokenStatusList::class,
            TokenStatusListDecodedData::class,
            TokenStatusListValue::class,
            TokenStatusListValueResolver::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('COSE', $source);
            self::assertStringNotContainsString('gzdecode', strtolower($source));
            self::assertStringNotContainsString('inflate_', strtolower($source));
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
        }
    }
}
