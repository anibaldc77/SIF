<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Problem;

use InvalidArgumentException;

final readonly class ProblemDetails
{
    /** @var array<string, null|bool|int|float|string|array<mixed>> */
    private array $extensions;

    /**
     * @param array<string, null|bool|int|float|string|array<mixed>> $extensions
     */
    public function __construct(
        private string $type,
        private string $title,
        private int $status,
        private ?string $detail = null,
        private ?string $instance = null,
        array $extensions = [],
    ) {
        if ($type === '' || $title === '') {
            throw new InvalidArgumentException('Problem type and title cannot be empty.');
        }
        if ($status < 400 || $status > 599) {
            throw new InvalidArgumentException('Problem status must be between 400 and 599.');
        }
        foreach ($extensions as $name => $value) {
            if (!is_string($name) || $name === '' || in_array($name, ['type', 'title', 'status', 'detail', 'instance'], true)) {
                throw new InvalidArgumentException('Problem extension names must be non-empty and non-reserved.');
            }
            self::assertStructured($value);
        }
        $this->extensions = $extensions;
    }

    public function type(): string { return $this->type; }
    public function title(): string { return $this->title; }
    public function status(): int { return $this->status; }
    public function detail(): ?string { return $this->detail; }
    public function instance(): ?string { return $this->instance; }

    /** @return array<string, null|bool|int|float|string|array<mixed>> */
    public function extensions(): array { return $this->extensions; }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
        ];
        if ($this->detail !== null) {
            $data['detail'] = $this->detail;
        }
        if ($this->instance !== null) {
            $data['instance'] = $this->instance;
        }

        return array_merge($data, $this->extensions);
    }

    private static function assertStructured(mixed $value): void
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return;
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException('Problem extensions must contain structured scalar values only.');
        }
        foreach ($value as $nested) {
            self::assertStructured($nested);
        }
    }
}
