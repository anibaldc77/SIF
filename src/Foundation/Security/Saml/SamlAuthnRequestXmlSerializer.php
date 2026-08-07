<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use DOMDocument;
use DOMElement;

final class SamlAuthnRequestXmlSerializer
{
    private const PROTOCOL_NS = 'urn:oasis:names:tc:SAML:2.0:protocol';
    private const ASSERTION_NS = 'urn:oasis:names:tc:SAML:2.0:assertion';

    public function serialize(SamlAuthnRequest $request): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $root = $document->createElementNS(
            self::PROTOCOL_NS,
            'samlp:AuthnRequest'
        );

        $root->setAttribute('ID', $request->id()->value());
        $root->setAttribute('Version', '2.0');
        $root->setAttribute(
            'IssueInstant',
            $request->issueInstant()->format('Y-m-d\TH:i:s\Z')
        );
        $root->setAttribute(
            'Destination',
            $request->destination()
        );
        $root->setAttribute(
            'AssertionConsumerServiceURL',
            $request->assertionConsumerServiceUrl()
        );

        if ($request->forceAuthn()) {
            $root->setAttribute('ForceAuthn', 'true');
        }

        $issuer = $document->createElementNS(
            self::ASSERTION_NS,
            'saml:Issuer'
        );
        $issuer->appendChild(
            $document->createTextNode(
                $request->issuer()->value()
            )
        );

        $root->appendChild($issuer);
        $document->appendChild($root);

        return $document->saveXML($root) ?: '';
    }
}
