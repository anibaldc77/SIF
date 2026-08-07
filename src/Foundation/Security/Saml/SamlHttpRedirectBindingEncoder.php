<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use RuntimeException;

final class SamlHttpRedirectBindingEncoder
{
    public function encode(
        SamlAuthnRequest $request,
        SamlRelayState $relayState
    ): SamlHttpRedirectRequest {
        $xml = (new SamlAuthnRequestXmlSerializer())->serialize(
            $request
        );

        $compressed = gzdeflate($xml);

        if ($compressed === false) {
            throw new RuntimeException(
                'Unable to DEFLATE SAML AuthnRequest.'
            );
        }

        return new SamlHttpRedirectRequest(
            $request->destination(),
            base64_encode($compressed),
            $relayState
        );
    }
}
