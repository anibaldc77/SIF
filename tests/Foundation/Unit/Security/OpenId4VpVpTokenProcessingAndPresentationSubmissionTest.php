<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpPresentationBindingPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpPresentationSubmissionValidatorInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpVpTokenProcessorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationSubmission;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationSubmissionAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResolvedPresentation;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpVpTokenEnvelope;

final class OpenId4VpVpTokenProcessingAndPresentationSubmissionTest extends TestCase
{
    public function testVpTokenEnvelopeKeepsTokensAndMetadataExplicit(): void
    {
        $envelope = new OpenId4VpVpTokenEnvelope(
            ['vp-token-001', 'vp-token-002'],
            ['response_mode' => 'direct_post']
        );

        self::assertSame(
            ['vp-token-001', 'vp-token-002'],
            $envelope->tokens()
        );
        self::assertSame(
            'direct_post',
            $envelope->metadata()['response_mode']
        );
    }

    public function testResolvedPresentationKeepsPresentationsAndCredentialIdsExplicit(): void
    {
        $presentation = new OpenId4VpResolvedPresentation(
            ['presentation-001'],
            ['credential-001']
        );

        self::assertSame(
            ['presentation-001'],
            $presentation->presentations()
        );
        self::assertSame(
            ['credential-001'],
            $presentation->credentialIds()
        );
    }

    public function testPresentationSubmissionKeepsDescriptorMapExplicit(): void
    {
        $submission = new OpenId4VpPresentationSubmission(
            'submission-001',
            'query-001',
            [
                [
                    'id' => 'identity',
                    'path' => '$[0]',
                ],
            ]
        );

        self::assertSame('submission-001', $submission->id());
        self::assertSame('query-001', $submission->definitionId());
        self::assertSame(
            'identity',
            $submission->descriptorMap()[0]['id']
        );
    }

    public function testSubmissionAssessmentTracksMissingDescriptors(): void
    {
        $assessment = new OpenId4VpPresentationSubmissionAssessment(
            false,
            ['address'],
            ['descriptor_map_incomplete']
        );

        self::assertFalse($assessment->valid());
        self::assertSame(
            ['address'],
            $assessment->missingDescriptorIds()
        );
        self::assertSame(
            ['descriptor_map_incomplete'],
            $assessment->violations()
        );
    }

    public function testVpTokenAndSubmissionContractsAreTyped(): void
    {
        $processor = new \ReflectionMethod(
            OpenId4VpVpTokenProcessorInterface::class,
            'process'
        );
        $validator = new \ReflectionMethod(
            OpenId4VpPresentationSubmissionValidatorInterface::class,
            'validate'
        );

        self::assertSame(
            OpenId4VpResolvedPresentation::class,
            (string) $processor->getReturnType()
        );
        self::assertSame(
            OpenId4VpPresentationSubmissionAssessment::class,
            (string) $validator->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                OpenId4VpPresentationBindingPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testProcessingLayerRemainsCredentialFormatAndCryptoNeutral(): void
    {
        foreach ([
            OpenId4VpVpTokenProcessorInterface::class,
            OpenId4VpPresentationSubmissionValidatorInterface::class,
            OpenId4VpPresentationBindingPolicyInterface::class,
            OpenId4VpVpTokenEnvelope::class,
            OpenId4VpResolvedPresentation::class,
            OpenId4VpPresentationSubmission::class,
            OpenId4VpPresentationSubmissionAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
        }
    }
}
