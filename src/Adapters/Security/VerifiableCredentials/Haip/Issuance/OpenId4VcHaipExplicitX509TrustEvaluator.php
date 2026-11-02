<?php
declare(strict_types=1);

namespace Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance;

use OpenSSLCertificate;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface;

/**
 * Evaluates ONLY the X.509 chain in a structurally parsed, still untrusted JWT.
 * Trust anchors are supplied explicitly by trusted application configuration.
 *
 * A true result does NOT establish JWS validity, issuer authorization,
 * key attestation validity, JWK binding, freshness, or revocation status.
 * This is not a KeyAttestationVerifierInterface implementation.
 */
final class OpenId4VcHaipExplicitX509TrustEvaluator
{
    /**
     * @param list<string> $trustedAnchorPems PEM certificates from trusted configuration
     */
    public function chainMatchesTrustedAnchors(
        OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface $jwt,
        array $trustedAnchorPems
    ): bool {
        if (!extension_loaded('openssl') || $trustedAnchorPems === [] || count($trustedAnchorPems) > 16) {
            return false;
        }

        $chainDer = $jwt->unverifiedCertificateDerChain();
        $certificates = [];
        $parsed = [];
        $seen = [];

        foreach ($chainDer as $der) {
            $hash = hash('sha256', $der);
            if (isset($seen[$hash])) {
                return false;
            }
            $seen[$hash] = true;
            $certificate = $this->readCertificate(self::derToPem($der));
            if ($certificate === null) {
                return false;
            }
            $certificates[] = $certificate[0];
            $parsed[] = $certificate[1];
        }

        $leaf = $parsed[0];
        if (!self::isCurrent($leaf) || !self::isSigningLeaf($leaf)) {
            return false;
        }

        // HAIP forbids a self-signed signing certificate.
        $leafPublicKey = @openssl_pkey_get_public($certificates[0]);
        if (
            $leafPublicKey === false
            || (($leaf['subject'] ?? null) === ($leaf['issuer'] ?? null)
                && @openssl_x509_verify($certificates[0], $leafPublicKey) === 1)
        ) {
            return false;
        }

        $count = count($certificates);
        for ($i = 1; $i < $count; $i++) {
            if (!self::isCurrent($parsed[$i]) || !self::isCertificateAuthority($parsed[$i])) {
                return false;
            }
            if (!self::issuerMatchesSubject($parsed[$i - 1], $parsed[$i])) {
                return false;
            }
            $issuerKey = @openssl_pkey_get_public($certificates[$i]);
            if ($issuerKey === false || @openssl_x509_verify($certificates[$i - 1], $issuerKey) !== 1) {
                return false;
            }
        }

        $anchors = [];
        foreach ($trustedAnchorPems as $pem) {
            $anchorDer = self::singlePemToDer($pem);
            if ($anchorDer === null || isset($seen[hash('sha256', $anchorDer)])) {
                // No trust anchor may be supplied within the untrusted x5c header.
                return false;
            }
            $anchor = $this->readCertificate(self::derToPem($anchorDer));
            if (
                $anchor === null
                || !self::isCurrent($anchor[1])
                || !self::isCertificateAuthority($anchor[1])
            ) {
                return false;
            }
            $anchors[] = [$anchorDer, $anchor[0], $anchor[1]];
        }

        // Require the supplied chain to end directly at a configured trust anchor.
        $last = $count - 1;
        $anchorMatches = false;
        foreach ($anchors as $anchor) {
            if (!self::issuerMatchesSubject($parsed[$last], $anchor[2])) {
                continue;
            }
            $key = @openssl_pkey_get_public($anchor[1]);
            if ($key !== false && @openssl_x509_verify($certificates[$last], $key) === 1) {
                $anchorMatches = true;
                break;
            }
        }
        if (!$anchorMatches) {
            return false;
        }

        $caFile = @tempnam(sys_get_temp_dir(), 'sif_haip_ca_');
        if ($caFile === false) {
            return false;
        }
        $intermediateFile = null;
        try {
            @chmod($caFile, 0600);
            $trustedBundle = '';
            foreach ($anchors as $anchor) {
                $trustedBundle .= self::derToPem($anchor[0]);
            }
            if (@file_put_contents($caFile, $trustedBundle) !== strlen($trustedBundle)) {
                return false;
            }

            if ($count > 1) {
                $intermediateFile = @tempnam(sys_get_temp_dir(), 'sif_haip_un_');
                if ($intermediateFile === false) {
                    return false;
                }
                @chmod($intermediateFile, 0600);
                $bundle = '';
                for ($i = 1; $i < $count; $i++) {
                    $bundle .= self::derToPem($chainDer[$i]);
                }
                if (@file_put_contents($intermediateFile, $bundle) !== strlen($bundle)) {
                    return false;
                }
            }

            // OpenSSL validates certification path, CA constraints, keyCertSign,
            // validity periods and pathLen, independently from signature matching.
            // Explicit ca_info avoids falling back to the system trust store.
            return @openssl_x509_checkpurpose(
                $certificates[0],
                X509_PURPOSE_ANY,
                [$caFile],
                $intermediateFile
            ) === true;
        } finally {
            @unlink($caFile);
            if (is_string($intermediateFile)) {
                @unlink($intermediateFile);
            }
        }
    }

    /** @return array{OpenSSLCertificate, array<string, mixed>}|null */
    private function readCertificate(string $pem): ?array
    {
        $certificate = @openssl_x509_read($pem);
        if ($certificate === false) {
            return null;
        }
        $info = @openssl_x509_parse($certificate);
        if (!is_array($info)) {
            return null;
        }
        return [$certificate, $info];
    }

    /** @param array<string, mixed> $info */
    private static function isCurrent(array $info): bool
    {
        $from = $info['validFrom_time_t'] ?? null;
        $until = $info['validTo_time_t'] ?? null;
        $now = time();
        return is_int($from) && is_int($until) && $from <= $now && $until >= $now;
    }

    /** @param array<string, mixed> $info */
    private static function isSigningLeaf(array $info): bool
    {
        $extensions = $info['extensions'] ?? null;
        if (!is_array($extensions)) {
            return false;
        }
        $constraints = $extensions['basicConstraints'] ?? null;
        $usage = $extensions['keyUsage'] ?? null;
        return is_string($constraints)
            && str_contains($constraints, 'CA:FALSE')
            && is_string($usage)
            && str_contains($usage, 'Digital Signature');
    }

    /** @param array<string, mixed> $info */
    private static function isCertificateAuthority(array $info): bool
    {
        $extensions = $info['extensions'] ?? null;
        if (!is_array($extensions)) {
            return false;
        }
        $constraints = $extensions['basicConstraints'] ?? null;
        $usage = $extensions['keyUsage'] ?? null;
        return is_string($constraints)
            && str_contains($constraints, 'CA:TRUE')
            && is_string($usage)
            && str_contains($usage, 'Certificate Sign');
    }

    /**
     * @param array<string, mixed> $subjectInfo
     * @param array<string, mixed> $issuerInfo
     */
    private static function issuerMatchesSubject(array $subjectInfo, array $issuerInfo): bool
    {
        $issuerName = $subjectInfo['issuer'] ?? null;
        $subjectName = $issuerInfo['subject'] ?? null;
        return is_array($issuerName) && is_array($subjectName) && $issuerName === $subjectName;
    }

    private static function derToPem(string $der): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
            . chunk_split(base64_encode($der), 64, "\n")
            . "-----END CERTIFICATE-----\n";
    }

    private static function singlePemToDer(string $pem): ?string
    {
        if (
            preg_match(
                '/\A\s*-----BEGIN CERTIFICATE-----\s*([A-Za-z0-9+\/=\r\n]+)-----END CERTIFICATE-----\s*\z/D',
                $pem,
                $matches
            ) !== 1
        ) {
            return null;
        }
        $base64 = preg_replace('/\s+/', '', $matches[1]);
        if (!is_string($base64)) {
            return null;
        }
        $der = base64_decode($base64, true);
        return $der !== false && $der !== '' && base64_encode($der) === $base64 ? $der : null;
    }
}
