<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim\Lifecycle;
use DateTimeImmutable;
final readonly class ScimProvisioningEvent {
    /** @param array<string, scalar|list<scalar>|null> $context */
    public function __construct(
        private ScimProvisioningAction $action,
        private ScimProvisioningTarget $target,
        private DateTimeImmutable $occurredAt,
        private array $context=[]
    ) {}
    public function action(): ScimProvisioningAction { return $this->action; }
    public function target(): ScimProvisioningTarget { return $this->target; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    /** @return array<string, scalar|list<scalar>|null> */
    public function context(): array { return $this->context; }
}
