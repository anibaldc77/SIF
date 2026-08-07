<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlHttpRedirectRequest
{
    public function __construct(
        private string $destination,
        private string $samlRequest,
        private SamlRelayState $relayState
    ) {
    }

    public function destination(): string
    {
        return $this->destination;
    }

    public function samlRequest(): string
    {
        return $this->samlRequest;
    }

    public function relayState(): SamlRelayState
    {
        return $this->relayState;
    }

    public function queryString(): string
    {
        return http_build_query(
            [
                'SAMLRequest' => $this->samlRequest,
                'RelayState' => $this->relayState->value(),
            ],
            '',
            '&',
            PHP_QUERY_RFC3986
        );
    }

    public function redirectUrl(): string
    {
        $separator = str_contains($this->destination, '?')
            ? '&'
            : '?';

        return $this->destination
            . $separator
            . $this->queryString();
    }
}
