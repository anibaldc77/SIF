<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnUserEntity
{
    public function __construct(
        private string $id,
        private string $name,
        private string $displayName
    ) {
        if (
            trim($this->id) === ''
            || trim($this->name) === ''
            || trim($this->displayName) === ''
        ) {
            throw new InvalidArgumentException('WebAuthn user entity is invalid.');
        }
    }

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
    public function displayName(): string { return $this->displayName; }
}
