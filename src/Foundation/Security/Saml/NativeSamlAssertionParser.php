<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use LibXMLError;
use Sif\Foundation\Security\Contracts\SamlAssertionParserInterface;
use Sif\Foundation\Security\Exceptions\InvalidSamlAssertionException;
use Throwable;

final class NativeSamlAssertionParser implements SamlAssertionParserInterface
{
    private const ASSERTION_NS = 'urn:oasis:names:tc:SAML:2.0:assertion';

    public function parse(string $xml): SamlAssertion
    {
        if (trim($xml) === '') {
            throw new InvalidSamlAssertionException(
                'SAML assertion XML must not be empty.'
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
                throw new InvalidSamlAssertionException(
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
            || $root->namespaceURI !== self::ASSERTION_NS
            || $root->localName !== 'Assertion'
        ) {
            throw new InvalidSamlAssertionException(
                'SAML assertion root must be saml:Assertion.'
            );
        }

        $id = trim($root->getAttribute('ID'));
        $issueInstant = trim($root->getAttribute('IssueInstant'));

        if ($id === '' || $issueInstant === '') {
            throw new InvalidSamlAssertionException(
                'SAML assertion requires ID and IssueInstant.'
            );
        }

        try {
            $parsedIssueInstant = new DateTimeImmutable($issueInstant);
        } catch (Throwable) {
            throw new InvalidSamlAssertionException(
                'SAML assertion IssueInstant is invalid.'
            );
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('saml', self::ASSERTION_NS);

        $issuer = trim(
            (string) $xpath->evaluate(
                'string(/saml:Assertion/saml:Issuer)'
            )
        );

        $nameId = trim(
            (string) $xpath->evaluate(
                'string(/saml:Assertion/saml:Subject/saml:NameID)'
            )
        );

        if ($issuer === '' || $nameId === '') {
            throw new InvalidSamlAssertionException(
                'SAML assertion requires Issuer and Subject NameID.'
            );
        }

        $nameIdFormat = trim(
            (string) $xpath->evaluate(
                'string(/saml:Assertion/saml:Subject/saml:NameID/@Format)'
            )
        );

        $conditionsNodes = $xpath->query(
            '/saml:Assertion/saml:Conditions'
        );

        $conditionsNode = $conditionsNodes !== false
            ? $conditionsNodes->item(0)
            : null;

        $notBefore = null;
        $notOnOrAfter = null;
        $audiences = [];

        if ($conditionsNode instanceof DOMElement) {
            $notBefore = $this->parseOptionalInstant(
                $conditionsNode->getAttribute('NotBefore'),
                'Conditions NotBefore'
            );
            $notOnOrAfter = $this->parseOptionalInstant(
                $conditionsNode->getAttribute('NotOnOrAfter'),
                'Conditions NotOnOrAfter'
            );

            $audienceNodes = $xpath->query(
                '/saml:Assertion/saml:Conditions'
                . '/saml:AudienceRestriction/saml:Audience'
            );

            if ($audienceNodes !== false) {
                foreach ($audienceNodes as $audienceNode) {
                    $value = trim($audienceNode->textContent);

                    if ($value !== '') {
                        $audiences[] = new SamlEntityId($value);
                    }
                }
            }
        }

        $subjectConfirmationData = null;

        $scdNodes = $xpath->query(
            '/saml:Assertion/saml:Subject'
            . '/saml:SubjectConfirmation'
            . '/saml:SubjectConfirmationData'
        );

        $scdNode = $scdNodes !== false
            ? $scdNodes->item(0)
            : null;

        if ($scdNode instanceof DOMElement) {
            $recipient = trim($scdNode->getAttribute('Recipient'));
            $inResponseTo = trim($scdNode->getAttribute('InResponseTo'));

            $subjectConfirmationData = new SamlSubjectConfirmationData(
                $recipient !== '' ? $recipient : null,
                $inResponseTo !== ''
                    ? new SamlRequestId($inResponseTo)
                    : null,
                $this->parseOptionalInstant(
                    $scdNode->getAttribute('NotOnOrAfter'),
                    'SubjectConfirmationData NotOnOrAfter'
                )
            );
        }

        return new SamlAssertion(
            new SamlAssertionId($id),
            $parsedIssueInstant,
            new SamlEntityId($issuer),
            new SamlNameId(
                $nameId,
                $nameIdFormat !== '' ? $nameIdFormat : null
            ),
            new SamlAssertionConditions(
                $notBefore,
                $notOnOrAfter,
                $audiences
            ),
            $subjectConfirmationData
        );
    }

    private function parseOptionalInstant(
        string $value,
        string $label
    ): ?DateTimeImmutable {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            throw new InvalidSamlAssertionException(
                'SAML assertion ' . $label . ' is invalid.'
            );
        }
    }

    /**
     * @param list<LibXMLError> $errors
     */
    private function formatXmlErrors(array $errors): string
    {
        if ($errors === []) {
            return 'SAML assertion XML is invalid.';
        }

        return 'SAML assertion XML is invalid: '
            . trim($errors[0]->message);
    }
}
