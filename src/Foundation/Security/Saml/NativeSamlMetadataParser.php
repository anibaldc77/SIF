<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DOMDocument;
use DOMElement;
use DOMXPath;
use LibXMLError;
use Sif\Foundation\Security\Contracts\SamlMetadataParserInterface;
use Sif\Foundation\Security\Exceptions\InvalidSamlMetadataException;

final class NativeSamlMetadataParser implements SamlMetadataParserInterface
{
    private const METADATA_NS = 'urn:oasis:names:tc:SAML:2.0:metadata';
    private const DS_NS = 'http://www.w3.org/2000/09/xmldsig#';

    public function parse(string $xml): SamlIdentityProviderMetadata
    {
        if (trim($xml) === '') {
            throw new InvalidSamlMetadataException(
                'SAML metadata XML must not be empty.'
            );
        }

        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadXML(
                $xml,
                LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_COMPACT
            );

            if (!$loaded) {
                throw new InvalidSamlMetadataException(
                    $this->formatXmlErrors(libxml_get_errors())
                );
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $root = $document->documentElement;

        if (
            !$root instanceof DOMElement
            || $root->namespaceURI !== self::METADATA_NS
            || $root->localName !== 'EntityDescriptor'
        ) {
            throw new InvalidSamlMetadataException(
                'SAML metadata root must be md:EntityDescriptor.'
            );
        }

        $entityId = trim($root->getAttribute('entityID'));

        if ($entityId === '') {
            throw new InvalidSamlMetadataException(
                'SAML metadata EntityDescriptor requires entityID.'
            );
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('md', self::METADATA_NS);
        $xpath->registerNamespace('ds', self::DS_NS);

        $idpNodes = $xpath->query(
            '/md:EntityDescriptor/md:IDPSSODescriptor'
        );

        if ($idpNodes === false || $idpNodes->length !== 1) {
            throw new InvalidSamlMetadataException(
                'SAML metadata must contain exactly one IDPSSODescriptor.'
            );
        }

        $ssoServices = $this->readEndpoints(
            $xpath,
            '/md:EntityDescriptor/md:IDPSSODescriptor/md:SingleSignOnService'
        );

        if ($ssoServices === []) {
            throw new InvalidSamlMetadataException(
                'SAML metadata requires at least one SingleSignOnService.'
            );
        }

        $logoutServices = $this->readEndpoints(
            $xpath,
            '/md:EntityDescriptor/md:IDPSSODescriptor/md:SingleLogoutService'
        );

        $fingerprints = $this->readSigningFingerprints($xpath);

        return new SamlIdentityProviderMetadata(
            new SamlEntityId($entityId),
            $ssoServices,
            $logoutServices,
            $fingerprints
        );
    }

    /**
     * @return list<SamlEndpoint>
     */
    private function readEndpoints(
        DOMXPath $xpath,
        string $query
    ): array {
        $nodes = $xpath->query($query);

        if ($nodes === false) {
            return [];
        }

        $endpoints = [];

        foreach ($nodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            $location = trim($node->getAttribute('Location'));

            if ($location === '') {
                throw new InvalidSamlMetadataException(
                    'SAML endpoint requires Location.'
                );
            }

            $binding = trim($node->getAttribute('Binding'));

            $endpoints[] = new SamlEndpoint(
                $location,
                $binding !== '' ? $binding : null
            );
        }

        return $endpoints;
    }

    /**
     * @return list<SamlCertificateFingerprint>
     */
    private function readSigningFingerprints(
        DOMXPath $xpath
    ): array {
        $nodes = $xpath->query(
            '/md:EntityDescriptor/md:IDPSSODescriptor/md:KeyDescriptor'
            . '[@use="signing" or not(@use)]'
            . '/ds:KeyInfo/ds:X509Data/ds:X509Certificate'
        );

        if ($nodes === false) {
            return [];
        }

        $fingerprints = [];

        foreach ($nodes as $node) {
            $certificate = new SamlX509Certificate(
                $node->textContent
            );

            $fingerprints[$certificate->fingerprint()->sha256()]
                = $certificate->fingerprint();
        }

        return array_values($fingerprints);
    }

    /**
     * @param list<LibXMLError> $errors
     */
    private function formatXmlErrors(array $errors): string
    {
        if ($errors === []) {
            return 'SAML metadata XML is invalid.';
        }

        return 'SAML metadata XML is invalid: '
            . trim($errors[0]->message);
    }
}
