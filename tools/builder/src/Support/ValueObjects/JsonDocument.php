<?php
declare(strict_types=1);

namespace Sif\Support\ValueObjects;

use JsonException;
use Sif\Support\Contracts\StringableInterface;
use Sif\Support\Exceptions\InvalidArgumentException;

final readonly class JsonDocument implements StringableInterface
{
    private function __construct(private mixed $value) {}
    public static function fromJson(string $json): self { try { return new self(json_decode($json, true, 512, JSON_THROW_ON_ERROR)); } catch (JsonException $exception) { throw new InvalidArgumentException('JSON document is invalid.', 0, $exception); } }
    public static function fromValue(mixed $value): self { try { json_encode($value, JSON_THROW_ON_ERROR); return new self($value); } catch (JsonException $exception) { throw new InvalidArgumentException('Value cannot be represented as JSON.', 0, $exception); } }
    public function value(): mixed { return $this->value; }
    public function toString(): string { try { return json_encode($this->value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); } catch (JsonException $exception) { throw new InvalidArgumentException('JSON document cannot be encoded.', 0, $exception); } }
    public function __toString(): string { return $this->toString(); }
}
