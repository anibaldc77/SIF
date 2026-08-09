<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\AuthorizationServer;

final readonly class OAuthDeviceAuthorization
{
    /**
     * @param list<OAuthScope> $scopes
     */
    public function __construct(
        private OAuthDeviceCode $deviceCode,
        private OAuthUserCode $userCode,
        private array $scopes,
        private OAuthDeviceAuthorizationStatus $status,
        private ?string $subject = null
    ) {
    }

    public function deviceCode(): OAuthDeviceCode
    {
        return $this->deviceCode;
    }

    public function userCode(): OAuthUserCode
    {
        return $this->userCode;
    }

    /**
     * @return list<OAuthScope>
     */
    public function scopes(): array
    {
        return $this->scopes;
    }

    public function status(): OAuthDeviceAuthorizationStatus
    {
        return $this->status;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }

    public function approved(): bool
    {
        return $this->status->value()
            === OAuthDeviceAuthorizationStatus::APPROVED;
    }
}
