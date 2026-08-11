<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpDigitalCredentialsContext
{
    /**
     * @param list<string> $supportedProtocols
     */
    public function __construct(
        private string $origin,
        private array $supportedProtocols,
        private bool $userMediationRequired = true
    ) {
        if (
            trim($this->origin) === ''
            || $this->supportedProtocols === []
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP Digital Credentials context is invalid.'
            );
        }
    }

    public function origin(): string
    {
        return $this->origin;
    }

    /**
     * @return list<string>
     */
    public function supportedProtocols(): array
    {
        return $this->supportedProtocols;
    }

    public function userMediationRequired(): bool
    {
        return $this->userMediationRequired;
    }
}
