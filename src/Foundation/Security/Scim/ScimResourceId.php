<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim;
use InvalidArgumentException;
final readonly class ScimResourceId
{
    public function __construct(private string $value)
    {
        if ($this->value === '' || strlen($this->value) > 255) {
            throw new InvalidArgumentException('SCIM resource id is invalid.');
        }
    }
    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
