<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim\Lifecycle;
use InvalidArgumentException;
final readonly class ScimProvisioningAction {
    public const CREATE='create', UPDATE='update', DEACTIVATE='deactivate', DELETE='delete',
        ADD_MEMBERSHIP='add-membership', REMOVE_MEMBERSHIP='remove-membership';
    public function __construct(private string $value) {
        if (!in_array($value,[self::CREATE,self::UPDATE,self::DEACTIVATE,self::DELETE,self::ADD_MEMBERSHIP,self::REMOVE_MEMBERSHIP],true)) {
            throw new InvalidArgumentException('SCIM provisioning action is invalid.');
        }
    }
    public function value(): string { return $this->value; }
}
