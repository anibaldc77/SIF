<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Session;

use DateTimeImmutable;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttribute;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;

final readonly class SessionPrincipalSnapshot
{
    public const VERSION = 1;

    /**
     * @param array<string, string|int|float|bool|null> $attributes
     */
    private function __construct(
        private string $identityId,
        private array $attributes,
        private string $method,
        private int $level,
        private string $authenticatedAt,
    ) {
    }

    public static function fromPrincipal(AuthenticatedPrincipal $principal): self
    {
        $data = $principal->toArray();

        return new self(
            $data['identity_id'],
            $data['attributes'],
            $data['authentication']['method'],
            $data['authentication']['level'],
            $data['authentication']['authenticated_at'],
        );
    }

    /** @param array<string, mixed> $payload */
    public static function fromArray(array $payload): self
    {
        if (($payload['version'] ?? null) !== self::VERSION) {
            throw new \InvalidArgumentException('Unsupported session principal snapshot version.');
        }

        $identityId = $payload['identity_id'] ?? null;
        $attributes = $payload['attributes'] ?? null;
        $authentication = $payload['authentication'] ?? null;

        if (!is_string($identityId) || !is_array($attributes) || !is_array($authentication)) {
            throw new \InvalidArgumentException('Malformed session principal snapshot.');
        }

        $method = $authentication['method'] ?? null;
        $level = $authentication['level'] ?? null;
        $authenticatedAt = $authentication['authenticated_at'] ?? null;

        if (!is_string($method) || !is_int($level) || !is_string($authenticatedAt)) {
            throw new \InvalidArgumentException('Malformed session authentication evidence.');
        }

        /** @var array<string, string|int|float|bool|null> $attributes */
        return new self($identityId, $attributes, $method, $level, $authenticatedAt);
    }

    public function toPrincipal(): AuthenticatedPrincipal
    {
        $items = [];
        foreach ($this->attributes as $name => $value) {
            $items[] = new PrincipalAttribute($name, $value);
        }

        return new AuthenticatedPrincipal(
            new Identity(new IdentityId($this->identityId)),
            new PrincipalAttributeCollection(...$items),
            new AuthenticationEvidence(
                new AuthenticationMethod($this->method),
                new AuthenticationLevel($this->level),
                new DateTimeImmutable($this->authenticatedAt),
            ),
        );
    }

    /**
     * @return array{
     *   version: int,
     *   identity_id: string,
     *   attributes: array<string, string|int|float|bool|null>,
     *   authentication: array{method: string, level: int, authenticated_at: string}
     * }
     */
    public function toArray(): array
    {
        return [
            'version' => self::VERSION,
            'identity_id' => $this->identityId,
            'attributes' => $this->attributes,
            'authentication' => [
                'method' => $this->method,
                'level' => $this->level,
                'authenticated_at' => $this->authenticatedAt,
            ],
        ];
    }
}
