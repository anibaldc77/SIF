<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnRelyingParty
{
    public function __construct(private string $id, private string $name)
    {
        if (trim($this->id) === '' || trim($this->name) === '') {
            throw new InvalidArgumentException('WebAuthn relying party is invalid.');
        }
    }

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
}
