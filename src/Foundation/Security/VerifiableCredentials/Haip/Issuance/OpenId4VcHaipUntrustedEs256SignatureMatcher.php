<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

/**
 * Checks only the ES256 signature against the UNTRUSTED x5c leaf certificate.
 *
 * A positive match NEVER establishes X.509 trust, certificate validity,
 * issuer authorization, key attestation validity, or acceptable freshness.
 * Do not expose this class as a KeyAttestationVerifierInterface implementation.
 */
final class OpenId4VcHaipUntrustedEs256SignatureMatcher
{
    public function matches(OpenId4VcHaipUnverifiedKeyAttestationJwt $jwt): bool
    {
        if (!extension_loaded('openssl')) {
            return false;
        }

        $chain = $jwt->unverifiedCertificateDerChain();
        $leafPem = "-----BEGIN CERTIFICATE-----\n"
            . chunk_split(base64_encode($chain[0]), 64, "\n")
            . "-----END CERTIFICATE-----\n";

        $certificate = @openssl_x509_read($leafPem);
        if ($certificate === false) {
            return false;
        }

        $publicKey = @openssl_pkey_get_public($certificate);
        if ($publicKey === false) {
            return false;
        }

        $details = @openssl_pkey_get_details($publicKey);
        if (
            !is_array($details)
            || ($details['type'] ?? null) !== OPENSSL_KEYTYPE_EC
            || ($details['bits'] ?? null) !== 256
            || ($details['ec']['curve_name'] ?? null) !== 'prime256v1'
        ) {
            return false;
        }

        $derSignature = self::joseToDer($jwt->rawSignature());
        if ($derSignature === null) {
            return false;
        }

        return @openssl_verify(
            $jwt->signingInput(),
            $derSignature,
            $publicKey,
            OPENSSL_ALGO_SHA256
        ) === 1;
    }

    private static function joseToDer(string $signature): ?string
    {
        if (strlen($signature) !== 64) {
            return null;
        }

        $r = self::integerToDer(substr($signature, 0, 32));
        $s = self::integerToDer(substr($signature, 32, 32));
        $sequence = $r . $s;

        return "\x30" . chr(strlen($sequence)) . $sequence;
    }

    private static function integerToDer(string $component): string
    {
        $significant = ltrim($component, "\x00");
        if ($significant === '') {
            $significant = "\x00";
        }

        if ((ord($significant[0]) & 0x80) !== 0) {
            $significant = "\x00" . $significant;
        }

        return "\x02" . chr(strlen($significant)) . $significant;
    }
}
