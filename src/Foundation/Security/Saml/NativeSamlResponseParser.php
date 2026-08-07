<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use LibXMLError;
use Sif\Foundation\Security\Contracts\SamlResponseParserInterface;
use Sif\Foundation\Security\Exceptions\InvalidSamlResponseException;
use Throwable;

final class NativeSamlResponseParser implements SamlResponseParserInterface
{
    private const PROTOCOL_NS = 'urn:oasis:names:tc:SAML:2.0:protocol';
    private const ASSERTION_NS = 'urn:oasis:names:tc:SAML:2.0:assertion';

    public function parse(string $xml): SamlResponse
    {
        if (trim($xml) === '') {
            throw new InvalidSamlResponseException(
                'SAML response XML must not be empty.'
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
                throw new InvalidSamlResponseException(
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
            || $root->namespaceURI !== self::PROTOCOL_NS
            || $root->localName !== 'Response'
        ) {
            throw new InvalidSamlResponseException(
                'SAML response root must be samlp:Response.'
            );
        }

        $id = trim($root->getAttribute('ID'));
        $issueInstant = trim($root->getAttribute('IssueInstant'));

        if ($id === '' || $issueInstant === '') {
            throw new InvalidSamlResponseException(
                'SAML response requires ID and IssueInstant.'
            );
        }

        try {
            $parsedIssueInstant = new DateTimeImmutable($issueInstant);
        } catch (Throwable) {
            throw new InvalidSamlResponseException(
                'SAML response IssueInstant is invalid.'
            );
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('samlp', self::PROTOCOL_NS);
        $xpath->registerNamespace('saml', self::ASSERTION_NS);

        $issuer = trim(
            (string) $xpath->evaluate(
                'string(/samlp:Response/saml:Issuer)'
            )
        );

        if ($issuer === '') {
            throw new InvalidSamlResponseException(
                'SAML response requires Issuer.'
            );
        }

        $status = trim(
            (string) $xpath->evaluate(
                'string(/samlp:Response/samlp:Status/samlp:StatusCode/@Value)'
            )
        );

        if ($status === '') {
            throw new InvalidSamlResponseException(
                'SAML response requires StatusCode.'
            );
        }

        $inResponseTo = trim(
            $root->getAttribute('InResponseTo')
        );
        $destination = trim(
            $root->getAttribute('Destination')
        );

        return new SamlResponse(
            new SamlResponseId($id),
            $parsedIssueInstant,
            new SamlEntityId($issuer),
            new SamlStatusCode($status),
            $inResponseTo !== ''
                ? new SamlRequestId($inResponseTo)
                : null,
            $destination !== ''
                ? $destination
                : null
        );
    }

    /**
     * @param list<LibXMLError> $errors
     */
    private function formatXmlErrors(array $errors): string
    {
        if ($errors === []) {
            return 'SAML response XML is invalid.';
        }

        return 'SAML response XML is invalid: '
            . trim($errors[0]->message);
    }
}
