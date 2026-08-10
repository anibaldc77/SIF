<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class SecurityEventProvisioningContext
{
    public function __construct(
        private SecurityEventSubject $subject,
        private string $operation,
        private ?string $resourceType = null,
        private ?string $resourceId = null
    ) {
        if (trim($this->operation) === '') {
            throw new InvalidArgumentException(
                'Security event provisioning operation is invalid.'
            );
        }
    }

    public function subject(): SecurityEventSubject
    {
        return $this->subject;
    }

    public function operation(): string
    {
        return $this->operation;
    }

    public function resourceType(): ?string
    {
        return $this->resourceType;
    }

    public function resourceId(): ?string
    {
        return $this->resourceId;
    }
}
