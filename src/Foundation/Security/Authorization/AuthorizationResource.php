<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization;

use Sif\Foundation\Security\Exceptions\InvalidAuthorizationRequestException;

final readonly class AuthorizationResource
{
    /** @var array<string, bool|int|string|null> */
    private array $attributes;

    /** @param array<string, bool|int|string|null> $attributes */
    public function __construct(
        private string $type,
        private ?string $id = null,
        array $attributes = []
    ) {
        if ($type === '' || preg_match('/^[a-z][a-z0-9._:-]{0,127}$/D', $type) !== 1) {
            throw new InvalidAuthorizationRequestException('Authorization resource type must be a stable lowercase identifier.');
        }

        if ($id !== null && ($id === '' || preg_match('/[\x00-\x1F\x7F]/', $id) === 1)) {
            throw new InvalidAuthorizationRequestException('Authorization resource identifier is invalid.');
        }

        foreach ($attributes as $name => $value) {
            if ($name === '' || preg_match('/^[a-z][a-z0-9._:-]{0,127}$/D', $name) !== 1) {
                throw new InvalidAuthorizationRequestException('Authorization resource attribute name is invalid.');
            }
        }

        ksort($attributes);
        $this->attributes = $attributes;
    }

    public function type(): string { return $this->type; }
    public function id(): ?string { return $this->id; }
    /** @return array<string, bool|int|string|null> */
    public function attributes(): array { return $this->attributes; }
}
