<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim\Lifecycle;
use InvalidArgumentException;
final readonly class ScimProvisioningTarget {
    public const USER='User', GROUP='Group';
    public function __construct(private string $resourceType, private string $resourceId) {
        if (!in_array($resourceType,[self::USER,self::GROUP],true) || trim($resourceId)==='') {
            throw new InvalidArgumentException('SCIM provisioning target is invalid.');
        }
    }
    public function resourceType(): string { return $this->resourceType; }
    public function resourceId(): string { return $this->resourceId; }
}
