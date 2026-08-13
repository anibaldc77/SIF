<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenIdFederationAccreditationBindingPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationTrustMarkResolverInterface;
use Sif\Foundation\Security\Contracts\OpenIdFederationTrustMarkValidationPolicyInterface;
use Sif\Foundation\Security\OpenIdFederation\TrustMarks\DefaultOpenIdFederationTrustMarkValidator;
use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationAccreditationBinding;
use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationTrustMark;
use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationTrustMarkValidationContext;
use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationTrustMarkValidationResult;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation\CredentialAccreditation;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation\CredentialAccreditationScope;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityRole;

final class OpenIdFederationTrustMarksAndAccreditationBindingTest extends TestCase
{
    private function trustMark(
        ?DateTimeImmutable $expiresAt = null
    ): OpenIdFederationTrustMark {
        return new OpenIdFederationTrustMark(
            'https://trustmark.example.test/qualified-issuer',
            'https://authority.example.test',
            'https://issuer.example.test',
            new DateTimeImmutable('2026-08-12T15:00:00Z'),
            $expiresAt,
            ['level' => 'qualified']
        );
    }

    private function accreditation(): CredentialAccreditation
    {
        return new CredentialAccreditation(
            'accreditation-001',
            new CredentialTrustEntityReference(
                'https://issuer.example.test',
                CredentialTrustEntityRole::Issuer,
                'ecosystem-alpha'
            ),
            new CredentialTrustEntityReference(
                'https://authority.example.test',
                CredentialTrustEntityRole::AccreditationAuthority,
                'ecosystem-alpha'
            ),
            new CredentialAccreditationScope(
                'qualified-issuer',
                ['IdentityCredential'],
                ['AR']
            ),
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
            new DateTimeImmutable('2026-12-31T23:59:59Z')
        );
    }

    public function testTrustMarkKeepsIssuerSubjectValidityAndClaimsExplicit(): void
    {
        $mark = $this->trustMark(
            new DateTimeImmutable('2026-08-12T17:00:00Z')
        );

        self::assertSame(
            'https://trustmark.example.test/qualified-issuer',
            $mark->trustMarkId()
        );
        self::assertSame(
            'https://authority.example.test',
            $mark->issuerEntityId()
        );
        self::assertSame(
            'https://issuer.example.test',
            $mark->subjectEntityId()
        );
        self::assertSame('qualified', $mark->claims()['level']);
    }

    public function testTrustMarkValidatorAcceptsCurrentExpectedMark(): void
    {
        $validator = new DefaultOpenIdFederationTrustMarkValidator();

        $result = $validator->validate(
            $this->trustMark(
                new DateTimeImmutable('2026-08-12T17:00:00Z')
            ),
            new OpenIdFederationTrustMarkValidationContext(
                new DateTimeImmutable('2026-08-12T16:00:00Z'),
                'https://issuer.example.test',
                'https://trustmark.example.test/qualified-issuer'
            )
        );

        self::assertTrue($result->valid());
        self::assertSame([], $result->violations());
    }

    public function testTrustMarkValidatorRejectsExpiredMark(): void
    {
        $validator = new DefaultOpenIdFederationTrustMarkValidator();

        $result = $validator->validate(
            $this->trustMark(
                new DateTimeImmutable('2026-08-12T15:30:00Z')
            ),
            new OpenIdFederationTrustMarkValidationContext(
                new DateTimeImmutable('2026-08-12T16:00:00Z')
            )
        );

        self::assertFalse($result->valid());
        self::assertContains('trust_mark_expired', $result->violations());
    }

    public function testTrustMarkValidatorRejectsUnexpectedSubject(): void
    {
        $validator = new DefaultOpenIdFederationTrustMarkValidator();

        $result = $validator->validate(
            $this->trustMark(),
            new OpenIdFederationTrustMarkValidationContext(
                new DateTimeImmutable('2026-08-12T16:00:00Z'),
                'https://other.example.test'
            )
        );

        self::assertFalse($result->valid());
        self::assertContains(
            'unexpected_trust_mark_subject',
            $result->violations()
        );
    }

    public function testAccreditationBindingKeepsProtocolMarkAndGenericAccreditationTogether(): void
    {
        $binding = new OpenIdFederationAccreditationBinding(
            $this->trustMark(),
            $this->accreditation()
        );

        self::assertSame(
            'https://issuer.example.test',
            $binding->accreditation()->subject()->entityId()
        );
        self::assertSame(
            $binding->trustMark()->subjectEntityId(),
            $binding->accreditation()->subject()->entityId()
        );
    }

    public function testAccreditationBindingRejectsDifferentAuthority(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $accreditation = new CredentialAccreditation(
            'accreditation-002',
            new CredentialTrustEntityReference(
                'https://issuer.example.test',
                CredentialTrustEntityRole::Issuer,
                'ecosystem-alpha'
            ),
            new CredentialTrustEntityReference(
                'https://different-authority.example.test',
                CredentialTrustEntityRole::AccreditationAuthority,
                'ecosystem-alpha'
            ),
            new CredentialAccreditationScope('qualified-issuer'),
            new DateTimeImmutable('2026-01-01T00:00:00Z')
        );

        new OpenIdFederationAccreditationBinding(
            $this->trustMark(),
            $accreditation
        );
    }

    public function testTrustMarkContractsAreTypedAndSeparated(): void
    {
        $resolver = new \ReflectionMethod(
            OpenIdFederationTrustMarkResolverInterface::class,
            'resolve'
        );
        $validator = new \ReflectionMethod(
            OpenIdFederationTrustMarkValidationPolicyInterface::class,
            'validate'
        );

        self::assertSame(
            OpenIdFederationTrustMark::class,
            (string) $resolver->getReturnType()
        );
        self::assertSame(
            OpenIdFederationTrustMarkValidationResult::class,
            (string) $validator->getReturnType()
        );
        self::assertTrue(
            (new \ReflectionClass(
                OpenIdFederationAccreditationBindingPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testArchitecturePreservesI1ToI4AndWp250Boundaries(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\OpenIdFederationEntityStatementVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationFetchProtocolInterface::class,
            \Sif\Foundation\Security\Contracts\OpenIdFederationMetadataPolicyApplicatorInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialAccreditationResolverInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustChainValidationPolicyInterface::class,
            \Sif\Foundation\Security\Contracts\CredentialTrustEnforcementPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(interface_exists($contract), $contract);
        }
    }

    public function testTrustMarkLayerRemainsJwtCryptoTransportAndStorageNeutral(): void
    {
        foreach ([
            OpenIdFederationTrustMarkResolverInterface::class,
            OpenIdFederationTrustMarkValidationPolicyInterface::class,
            OpenIdFederationAccreditationBindingPolicyInterface::class,
            OpenIdFederationTrustMark::class,
            OpenIdFederationTrustMarkValidationContext::class,
            OpenIdFederationTrustMarkValidationResult::class,
            OpenIdFederationAccreditationBinding::class,
            DefaultOpenIdFederationTrustMarkValidator::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}
