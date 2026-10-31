<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;
use JsonException;
use stdClass;

/**
 * Structural JOSE parser. All returned fields are UNTRUSTED until signature,
 * certificate chain, issuer policy and freshness have been verified.
 *
 * This initial SIF adapter deliberately supports only ES256 and x5c.
 * It does not represent the complete range of algorithms allowed by OID4VCI.
 */
final readonly class OpenId4VcHaipUnverifiedKeyAttestationJwt
{
    private const MAX_COMPACT_LENGTH = 131072;
    private const MAX_CERTIFICATE_COUNT = 8;

    /** @var array<string, mixed> */
    private array $header;

    /** @var array<string, mixed> */
    private array $claims;

    /** @var non-empty-list<string> */
    private array $certificateDerChain;

    /**
     * @param array<string, mixed> $header
     * @param array<string, mixed> $claims
     * @param non-empty-list<string> $certificateDerChain
     */
    private function __construct(
        private string $signingInput,
        private string $rawSignature,
        array $header,
        array $claims,
        array $certificateDerChain
    ) {
        $this->header = $header;
        $this->claims = $claims;
        $this->certificateDerChain = $certificateDerChain;
    }

    public static function parse(string $compactJwt): self
    {
        if ($compactJwt === '' || strlen($compactJwt) > self::MAX_COMPACT_LENGTH) {
            throw new InvalidArgumentException('Invalid key attestation JWT size.');
        }

        $segments = explode('.', $compactJwt);
        if (count($segments) !== 3) {
            throw new InvalidArgumentException('Key attestation requires compact JWS.');
        }

        [$encodedHeader, $encodedClaims, $encodedSignature] = $segments;
        $headerObject = self::decodeJsonObject(self::decodeBase64Url($encodedHeader));
        $claimsObject = self::decodeJsonObject(self::decodeBase64Url($encodedClaims));
        $signature = self::decodeBase64Url($encodedSignature);

        if (($headerObject->typ ?? null) !== 'key-attestation+jwt') {
            throw new InvalidArgumentException('Unexpected key attestation JOSE typ.');
        }

        if (($headerObject->alg ?? null) !== 'ES256') {
            throw new InvalidArgumentException('Unsupported key attestation algorithm.');
        }

        // Reject unimplemented critical extensions and remote/untrusted key selectors.
        foreach (['crit', 'b64', 'jku', 'x5u', 'jwk'] as $forbidden) {
            if (property_exists($headerObject, $forbidden)) {
                throw new InvalidArgumentException('Forbidden key attestation JOSE header.');
            }
        }

        if (strlen($signature) !== 64) {
            throw new InvalidArgumentException('Invalid ES256 JWS signature length.');
        }

        $certificates = $headerObject->x5c ?? null;
        if (
            !is_array($certificates)
            || count($certificates) < 1
            || count($certificates) > self::MAX_CERTIFICATE_COUNT
        ) {
            throw new InvalidArgumentException('Invalid key attestation x5c chain.');
        }

        $derChain = [];
        foreach ($certificates as $certificate) {
            if (
                !is_string($certificate)
                || $certificate === ''
                || strlen($certificate) > 32768
            ) {
                throw new InvalidArgumentException('Invalid x5c certificate encoding.');
            }

            $der = base64_decode($certificate, true);
            if ($der === false || $der === '' || base64_encode($der) !== $certificate) {
                throw new InvalidArgumentException('Invalid x5c certificate encoding.');
            }
            $derChain[] = $der;
        }

        if (!is_int($claimsObject->iat ?? null)) {
            throw new InvalidArgumentException('Key attestation iat is required.');
        }

        if (property_exists($claimsObject, 'exp') && !is_int($claimsObject->exp)) {
            throw new InvalidArgumentException('Invalid key attestation exp.');
        }

        if (
            property_exists($claimsObject, 'nonce')
            && (!is_string($claimsObject->nonce) || $claimsObject->nonce === '')
        ) {
            throw new InvalidArgumentException('Invalid key attestation nonce.');
        }

        if (
            property_exists($claimsObject, 'iss')
            && (!is_string($claimsObject->iss) || trim($claimsObject->iss) === '')
        ) {
            throw new InvalidArgumentException('Invalid key attestation issuer.');
        }

        $keys = $claimsObject->attested_keys ?? null;
        if (!is_array($keys) || $keys === []) {
            throw new InvalidArgumentException('Missing key attestation attested_keys.');
        }

        foreach ($keys as $key) {
            if (
                !$key instanceof stdClass
                || !is_string($key->kty ?? null)
                || trim($key->kty) === ''
            ) {
                throw new InvalidArgumentException('Malformed key attestation JWK.');
            }
        }

        /** @var non-empty-list<string> $derChain */
        return new self(
            $encodedHeader . '.' . $encodedClaims,
            $signature,
            (array) $headerObject,
            (array) $claimsObject,
            $derChain
        );
    }

    public function signingInput(): string
    {
        return $this->signingInput;
    }

    public function rawSignature(): string
    {
        return $this->rawSignature;
    }

    /** @return array<string, mixed> */
    public function unverifiedHeader(): array
    {
        return $this->header;
    }

    /** @return array<string, mixed> */
    public function unverifiedClaims(): array
    {
        return $this->claims;
    }

    /** @return non-empty-list<string> */
    public function unverifiedCertificateDerChain(): array
    {
        return $this->certificateDerChain;
    }

    private static function decodeBase64Url(string $input): string
    {
        if ($input === '' || preg_match('/^[A-Za-z0-9_-]+$/D', $input) !== 1) {
            throw new InvalidArgumentException('Invalid base64url JWS segment.');
        }

        $decoded = base64_decode(
            strtr($input, '-_', '+/') . str_repeat('=', (4 - strlen($input) % 4) % 4),
            true
        );

        if (
            $decoded === false
            || rtrim(strtr(base64_encode($decoded), '+/', '-_'), '=') !== $input
        ) {
            throw new InvalidArgumentException('Non-canonical base64url JWS segment.');
        }

        return $decoded;
    }

    private static function decodeJsonObject(string $json): stdClass
    {
        try {
            $decoded = json_decode($json, false, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Malformed JWS JSON.', 0, $exception);
        }

        if (!$decoded instanceof stdClass) {
            throw new InvalidArgumentException('JWS header and claims must be objects.');
        }

        return $decoded;
    }
}
