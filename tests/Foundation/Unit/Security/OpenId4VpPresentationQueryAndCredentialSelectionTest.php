<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpCredentialCandidateProviderInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpCredentialSelectionPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpPresentationQueryEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpCredentialCandidate;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpCredentialSelection;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationQuery;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationQueryAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationRequirement;

final class OpenId4VpPresentationQueryAndCredentialSelectionTest extends TestCase
{
    public function testPresentationRequirementKeepsFormatsAndConstraintsExplicit(): void
    {
        $requirement = new OpenId4VpPresentationRequirement(
            'identity',
            ['dc+sd-jwt', 'mso_mdoc'],
            ['claim' => 'given_name']
        );

        self::assertSame('identity', $requirement->id());
        self::assertSame(
            ['dc+sd-jwt', 'mso_mdoc'],
            $requirement->acceptedFormats()
        );
        self::assertSame(
            'given_name',
            $requirement->constraints()['claim']
        );
        self::assertTrue($requirement->required());
    }

    public function testPresentationQueryKeepsRequirementsExplicit(): void
    {
        $query = new OpenId4VpPresentationQuery(
            'query-001',
            [
                new OpenId4VpPresentationRequirement(
                    'identity',
                    ['dc+sd-jwt']
                ),
            ]
        );

        self::assertSame('query-001', $query->queryId());
        self::assertCount(1, $query->requirements());
    }

    public function testCredentialCandidateKeepsFormatAndAttributesExplicit(): void
    {
        $candidate = new OpenId4VpCredentialCandidate(
            'credential-001',
            ['dc+sd-jwt'],
            ['issuer' => 'https://issuer.example.test']
        );

        self::assertSame('credential-001', $candidate->credentialId());
        self::assertSame(['dc+sd-jwt'], $candidate->formats());
        self::assertSame(
            'https://issuer.example.test',
            $candidate->attributes()['issuer']
        );
    }

    public function testCredentialSelectionKeepsSatisfiedRequirementsExplicit(): void
    {
        $selection = new OpenId4VpCredentialSelection(
            'query-001',
            ['credential-001'],
            ['identity']
        );

        self::assertSame('query-001', $selection->queryId());
        self::assertSame(
            ['credential-001'],
            $selection->credentialIds()
        );
        self::assertSame(
            ['identity'],
            $selection->satisfiedRequirementIds()
        );
    }

    public function testQueryAssessmentKeepsMissingRequirementsExplicit(): void
    {
        $assessment = new OpenId4VpPresentationQueryAssessment(
            false,
            ['address'],
            ['identity requirement matched']
        );

        self::assertFalse($assessment->satisfied());
        self::assertSame(
            ['address'],
            $assessment->missingRequirementIds()
        );
        self::assertSame(
            ['identity requirement matched'],
            $assessment->warnings()
        );
    }

    public function testSelectionContractsAreTyped(): void
    {
        $selection = new \ReflectionMethod(
            OpenId4VpCredentialSelectionPolicyInterface::class,
            'select'
        );
        $evaluation = new \ReflectionMethod(
            OpenId4VpPresentationQueryEvaluatorInterface::class,
            'evaluate'
        );
        $candidates = new \ReflectionMethod(
            OpenId4VpCredentialCandidateProviderInterface::class,
            'candidates'
        );

        self::assertSame(
            OpenId4VpCredentialSelection::class,
            (string) $selection->getReturnType()
        );
        self::assertSame(
            OpenId4VpPresentationQueryAssessment::class,
            (string) $evaluation->getReturnType()
        );
        self::assertSame(
            'array',
            (string) $candidates->getReturnType()
        );
    }

    public function testSelectionLayerRemainsCredentialFormatAndStorageNeutral(): void
    {
        foreach ([
            OpenId4VpCredentialSelectionPolicyInterface::class,
            OpenId4VpPresentationQueryEvaluatorInterface::class,
            OpenId4VpCredentialCandidateProviderInterface::class,
            OpenId4VpPresentationRequirement::class,
            OpenId4VpPresentationQuery::class,
            OpenId4VpCredentialCandidate::class,
            OpenId4VpCredentialSelection::class,
            OpenId4VpPresentationQueryAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
