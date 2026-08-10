<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiAuthorizationServerSecurityRequirements
{
    /** @param list<string> $supportedCodeChallengeMethods */
    public function __construct(
        private bool $requireParEndpoint = true,
        private bool $requireIssuerMetadata = true,
        private bool $requireSenderConstrainedTokens = true,
        private array $supportedCodeChallengeMethods = ['S256']
    ) {}

    public function requireParEndpoint(): bool { return $this->requireParEndpoint; }
    public function requireIssuerMetadata(): bool { return $this->requireIssuerMetadata; }
    public function requireSenderConstrainedTokens(): bool { return $this->requireSenderConstrainedTokens; }

    /** @return list<string> */
    public function supportedCodeChallengeMethods(): array { return $this->supportedCodeChallengeMethods; }
}
