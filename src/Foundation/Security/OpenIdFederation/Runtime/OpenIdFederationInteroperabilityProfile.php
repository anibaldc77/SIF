<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Runtime;

final readonly class OpenIdFederationInteroperabilityProfile
{
    public function __construct(
        private bool $openidConnect = true,
        private bool $openid4Vci = true,
        private bool $openid4Vp = true,
        private bool $walletMetadata = true
    ) {
    }

    public function openidConnect(): bool
    {
        return $this->openidConnect;
    }

    public function openid4Vci(): bool
    {
        return $this->openid4Vci;
    }

    public function openid4Vp(): bool
    {
        return $this->openid4Vp;
    }

    public function walletMetadata(): bool
    {
        return $this->walletMetadata;
    }

    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'openid_connect' => $this->openidConnect,
            'openid4vci' => $this->openid4Vci,
            'openid4vp' => $this->openid4Vp,
            'wallet_metadata' => $this->walletMetadata,
        ];
    }
}
