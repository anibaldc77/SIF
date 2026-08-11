<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpRequestObjectVerifierInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpRequestUriResolverInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpVerifierAuthenticationPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationRequestSource;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpRequestObject;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpRequestUri;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpVerifierAuthenticationResult;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpVerifierIdentity;

final class OpenId4VpRequestObjectRequestUriAndVerifierAuthenticationTest extends TestCase
{
    public function testAuthorizationRequestSourcesAreExplicit(): void
    {
        self::assertSame(
            'inline_parameters',
            OpenId4VpAuthorizationRequestSource::InlineParameters->value
        );
        self::assertSame(
            'request_object',
            OpenId4VpAuthorizationRequestSource::RequestObject->value
        );
        self::assertSame(
            'request_uri',
            OpenId4VpAuthorizationRequestSource::RequestUri->value
        );
    }

    public function testRequestObjectKeepsCryptographicHintsExplicit(): void
    {
        $object = new OpenId4VpRequestObject(
            'eyJhbGciOiJFUzI1NiJ9.payload.signature',
            'key-001',
            'ES256'
        );

        self::assertSame(
            'eyJhbGciOiJFUzI1NiJ9.payload.signature',
            $object->serialized()
        );
        self::assertSame('key-001', $object->keyId());
        self::assertSame('ES256', $object->algorithm());
    }

    public function testRequestUriRequiresAValidUri(): void
    {
        $uri = new OpenId4VpRequestUri(
            'https://verifier.example.test/request/123'
        );

        self::assertSame(
            'https://verifier.example.test/request/123',
            $uri->value()
        );
    }

    public function testVerifierIdentityKeepsAuthenticationMethodExplicit(): void
    {
        $identity = new OpenId4VpVerifierIdentity(
            'https://verifier.example.test',
            'signed_request_object',
            ['trust_framework' => 'example']
        );

        self::assertSame(
            'https://verifier.example.test',
            $identity->identifier()
        );
        self::assertSame(
            'signed_request_object',
            $identity->authenticationMethod()
        );
        self::assertSame(
            'example',
            $identity->attributes()['trust_framework']
        );
    }

    public function testVerifierAuthenticationResultSeparatesIdentityFromDecision(): void
    {
        $identity = new OpenId4VpVerifierIdentity(
            'https://verifier.example.test',
            'signed_request_object'
        );
        $result = new OpenId4VpVerifierAuthenticationResult(
            true,
            $identity,
            ['trust policy evaluation remains external']
        );

        self::assertTrue($result->authenticated());
        self::assertSame($identity, $result->identity());
        self::assertSame(
            ['trust policy evaluation remains external'],
            $result->warnings()
        );
    }

    public function testRequestAndVerifierAuthenticationContractsAreTyped(): void
    {
        $verify = new \ReflectionMethod(
            OpenId4VpRequestObjectVerifierInterface::class,
            'verify'
        );
        $resolve = new \ReflectionMethod(
            OpenId4VpRequestUriResolverInterface::class,
            'resolve'
        );
        $accepts = new \ReflectionMethod(
            OpenId4VpVerifierAuthenticationPolicyInterface::class,
            'accepts'
        );

        self::assertSame(
            OpenId4VpVerifierAuthenticationResult::class,
            (string) $verify->getReturnType()
        );
        self::assertSame(
            OpenId4VpRequestObject::class,
            (string) $resolve->getReturnType()
        );
        self::assertSame('bool', (string) $accepts->getReturnType());
    }

    public function testFoundationRemainsTransportCryptoLibraryAndFrameworkNeutral(): void
    {
        foreach ([
            OpenId4VpRequestObjectVerifierInterface::class,
            OpenId4VpRequestUriResolverInterface::class,
            OpenId4VpVerifierAuthenticationPolicyInterface::class,
            OpenId4VpRequestObject::class,
            OpenId4VpRequestUri::class,
            OpenId4VpVerifierIdentity::class,
            OpenId4VpVerifierAuthenticationResult::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}
