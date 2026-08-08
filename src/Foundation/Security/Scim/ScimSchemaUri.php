<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim;
use InvalidArgumentException;
final readonly class ScimSchemaUri
{
    public function __construct(private string $value)
    {
        if ($this->value === '' || strlen($this->value) > 1024 || !str_contains($this->value, ':')) {
            throw new InvalidArgumentException('SCIM schema URI is invalid.');
        }
    }
    public function value(): string { return $this->value; }
}
