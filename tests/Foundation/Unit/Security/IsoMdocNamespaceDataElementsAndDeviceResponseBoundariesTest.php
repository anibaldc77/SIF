<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IsoMdocDeviceResponseParserInterface;
use Sif\Foundation\Security\Contracts\IsoMdocDocumentPolicyInterface;
use Sif\Foundation\Security\Contracts\IsoMdocNamespacePolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDataElement;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceResponse;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceSignedData;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDocument;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocIssuerSignedData;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocNamespace;

final class IsoMdocNamespaceDataElementsAndDeviceResponseBoundariesTest extends TestCase
{
    public function testDataElementKeepsIdentifierAndValueExplicit(): void
    {
        $element = new IsoMdocDataElement(
            'family_name',
            'Example'
        );

        self::assertSame('family_name', $element->identifier());
        self::assertSame('Example', $element->value());
    }

    public function testNamespaceKeepsNameAndElementsExplicit(): void
    {
        $namespace = new IsoMdocNamespace(
            'org.iso.18013.5.1',
            [
                new IsoMdocDataElement(
                    'given_name',
                    'Alice'
                ),
            ]
        );

        self::assertSame(
            'org.iso.18013.5.1',
            $namespace->name()
        );
        self::assertCount(1, $namespace->elements());
        self::assertSame(
            'given_name',
            $namespace->elements()[0]->identifier()
        );
    }

    public function testIssuerSignedDataKeepsDocumentTypeAndNamespacesExplicit(): void
    {
        $issuerSigned = new IsoMdocIssuerSignedData(
            'org.iso.18013.5.1.mDL',
            [
                new IsoMdocNamespace(
                    'org.iso.18013.5.1',
                    []
                ),
            ],
            ['algorithm' => 'ES256']
        );

        self::assertSame(
            'org.iso.18013.5.1.mDL',
            $issuerSigned->documentType()
        );
        self::assertCount(1, $issuerSigned->namespaces());
        self::assertSame(
            'ES256',
            $issuerSigned->issuerAuthentication()['algorithm']
        );
    }

    public function testDocumentSeparatesIssuerAndDeviceSignedData(): void
    {
        $issuerSigned = new IsoMdocIssuerSignedData(
            'org.iso.18013.5.1.mDL'
        );
        $deviceSigned = new IsoMdocDeviceSignedData(
            [],
            ['method' => 'device_signature']
        );
        $document = new IsoMdocDocument(
            'org.iso.18013.5.1.mDL',
            $issuerSigned,
            $deviceSigned
        );

        self::assertSame(
            'org.iso.18013.5.1.mDL',
            $document->documentType()
        );
        self::assertSame($issuerSigned, $document->issuerSigned());
        self::assertSame($deviceSigned, $document->deviceSigned());
    }

    public function testDeviceResponseKeepsVersionDocumentsAndMetadataExplicit(): void
    {
        $document = new IsoMdocDocument(
            'org.iso.18013.5.1.mDL',
            new IsoMdocIssuerSignedData(
                'org.iso.18013.5.1.mDL'
            )
        );
        $response = new IsoMdocDeviceResponse(
            '1.0',
            [$document],
            ['status' => 0]
        );

        self::assertSame('1.0', $response->version());
        self::assertCount(1, $response->documents());
        self::assertSame(0, $response->metadata()['status']);
    }

    public function testMdocContractsAreTypedAndSeparated(): void
    {
        $parser = new \ReflectionMethod(
            IsoMdocDeviceResponseParserInterface::class,
            'parse'
        );

        self::assertSame(
            IsoMdocDeviceResponse::class,
            (string) $parser->getReturnType()
        );

        foreach ([
            IsoMdocNamespacePolicyInterface::class,
            IsoMdocDocumentPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testMdocModelRemainsCborCoseCryptoAndTransportNeutral(): void
    {
        foreach ([
            IsoMdocDeviceResponseParserInterface::class,
            IsoMdocNamespacePolicyInterface::class,
            IsoMdocDocumentPolicyInterface::class,
            IsoMdocDataElement::class,
            IsoMdocNamespace::class,
            IsoMdocIssuerSignedData::class,
            IsoMdocDeviceSignedData::class,
            IsoMdocDocument::class,
            IsoMdocDeviceResponse::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('CBOR', $source);
            self::assertStringNotContainsString('COSE', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
        }
    }
}
