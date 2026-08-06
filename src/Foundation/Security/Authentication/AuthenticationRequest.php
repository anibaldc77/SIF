<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Security\Contracts\CredentialInterface;

final readonly class AuthenticationRequest
{
    private DateTimeImmutable $requestedAt;

    public function __construct(
        private AuthenticationRequestId $id,
        private CredentialInterface $credential,
        DateTimeImmutable $requestedAt
    ) {
        $this->requestedAt = $requestedAt->setTimezone(new DateTimeZone('UTC'));
    }

    public function id(): AuthenticationRequestId
    {
        return $this->id;
    }

    public function credential(): CredentialInterface
    {
        return $this->credential;
    }

    public function requestedAt(): DateTimeImmutable
    {
        return $this->requestedAt;
    }

    /** @return array{request_id: string, credential_type: string, requested_at: string} */
    public function metadata(): array
    {
        return [
            'request_id' => $this->id->value(),
            'credential_type' => $this->credential->type()->value(),
            'requested_at' => $this->requestedAt->format('Y-m-d\TH:i:s.uP'),
        ];
    }
}
