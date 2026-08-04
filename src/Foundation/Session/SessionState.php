<?php

declare(strict_types=1);

namespace Sif\Foundation\Session;

use DateTimeImmutable;
use Sif\Foundation\Session\Flash\FlashBag;

final class SessionState
{
    /** @var array<string, mixed> */
    private array $data;
    private FlashBag $flash;
    private bool $dirty = false;
    private bool $destroyed = false;
    private bool $regenerationRequested = false;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $flashAvailable
     */
    public function __construct(
        private SessionId $id,
        array $data,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $lastAccessedAt,
        private int $version = 1,
        private bool $new = false,
        array $flashAvailable = [],
        private ?DateTimeImmutable $lastRegenerated = null,
    ) {
        $this->data = $data;
        $this->flash = new FlashBag($flashAvailable);
        $this->lastRegenerated ??= $createdAt;
    }

    public function id(): SessionId { return $this->id; }
    public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }
    public function has(string $key): bool { return array_key_exists($key, $this->data); }
    /** @return array<string, mixed> */
    public function all(): array { return $this->data; }

    public function put(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
        $this->dirty = true;
    }

    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
            $this->dirty = true;
        }
    }

    public function flash(string $key, mixed $value): void
    {
        $this->flash->put($key, $value);
        $this->dirty = true;
    }

    public function flashGet(string $key, mixed $default = null): mixed { return $this->flash->get($key, $default); }
    public function flashHas(string $key): bool { return $this->flash->has($key); }
    /** @return array<string, mixed> */
    public function flashAll(): array { return $this->flash->all(); }
    public function keepFlash(string $key): void { $this->flash->keep($key); $this->dirty = true; }
    public function reflash(): void { $this->flash->reflash(); $this->dirty = true; }

    public function destroy(): void
    {
        $this->data = [];
        $this->destroyed = true;
        $this->dirty = true;
    }

    public function requestRegeneration(): void { $this->regenerationRequested = true; }
    public function regenerationRequested(): bool { return $this->regenerationRequested; }
    public function destroyed(): bool { return $this->destroyed; }
    public function dirty(): bool { return $this->dirty; }
    public function isNew(): bool { return $this->new; }
    public function lastRegeneratedAt(): DateTimeImmutable { return $this->lastRegenerated ?? $this->createdAt; }

    public function regenerate(SessionId $id, ?DateTimeImmutable $now = null): SessionId
    {
        $previous = $this->id;
        $this->id = $id;
        $this->lastRegenerated = $now ?? $this->lastAccessedAt;
        $this->regenerationRequested = false;
        $this->dirty = true;
        $this->new = true;
        return $previous;
    }

    public function touch(DateTimeImmutable $now): void
    {
        $this->lastAccessedAt = $now;
        $this->dirty = true;
    }

    public function toRecord(): SessionRecord
    {
        return new SessionRecord(
            $this->id,
            $this->data,
            $this->createdAt,
            $this->lastAccessedAt,
            $this->version + 1,
            $this->flash->nextRequestData(),
            $this->lastRegeneratedAt(),
        );
    }
}
