<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Casting;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JsonException;
use Sif\Foundation\Model\Exceptions\InvalidModelAttributeValueException;
use Sif\Foundation\Model\Metadata\ModelAttributeCast;
use Sif\Foundation\Model\Metadata\ModelAttributeDefinition;

final class ModelAttributeCaster
{
    public function cast(ModelAttributeDefinition $attribute, mixed $value): mixed
    {
        if ($value === null) {
            if (!$attribute->nullable()) {
                throw new InvalidModelAttributeValueException(
                    sprintf('Attribute "%s" does not accept null.', $attribute->name()->value()),
                );
            }

            return null;
        }

        try {
            return match ($attribute->cast()) {
                ModelAttributeCast::Mixed => $value,
                ModelAttributeCast::String => $this->toString($value),
                ModelAttributeCast::Integer => $this->toInteger($value),
                ModelAttributeCast::Float => $this->toFloat($value),
                ModelAttributeCast::Boolean => $this->toBoolean($value),
                ModelAttributeCast::Array => $this->toArray($value),
                ModelAttributeCast::Json => $this->toJsonArray($value),
                ModelAttributeCast::DateTime => $this->toDateTime($value),
                ModelAttributeCast::ImmutableDateTime => $this->toImmutableDateTime($value),
            };
        } catch (InvalidModelAttributeValueException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new InvalidModelAttributeValueException(
                sprintf('Attribute "%s" cannot be cast as %s.', $attribute->name()->value(), $attribute->cast()->value),
                previous: $exception,
            );
        }
    }

    public function serialize(ModelAttributeDefinition $attribute, mixed $value): mixed
    {
        $cast = $this->cast($attribute, $value);

        if ($cast instanceof DateTimeInterface) {
            return $cast->format(DateTimeInterface::ATOM);
        }

        return $cast;
    }

    private function toString(mixed $value): string
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            throw new InvalidModelAttributeValueException('Value is not string-compatible.');
        }

        return (string) $value;
    }

    private function toInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/D', $value) === 1) {
            return (int) $value;
        }

        throw new InvalidModelAttributeValueException('Value is not an integer.');
    }

    private function toFloat(mixed $value): float
    {
        if (!is_int($value) && !is_float($value) && !(is_string($value) && is_numeric($value))) {
            throw new InvalidModelAttributeValueException('Value is not numeric.');
        }

        $float = (float) $value;
        if (!is_finite($float)) {
            throw new InvalidModelAttributeValueException('Value must be finite.');
        }

        return $float;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === 0 || $value === 1 || $value === '0' || $value === '1') {
            return (bool) $value;
        }

        throw new InvalidModelAttributeValueException('Value is not boolean-compatible.');
    }

    /** @return array<array-key, mixed> */
    private function toArray(mixed $value): array
    {
        if (!is_array($value)) {
            throw new InvalidModelAttributeValueException('Value is not an array.');
        }

        return $value;
    }

    /** @return array<array-key, mixed> */
    private function toJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            throw new InvalidModelAttributeValueException('JSON value must be a string or array.');
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidModelAttributeValueException('JSON value is invalid.', previous: $exception);
        }

        if (!is_array($decoded)) {
            throw new InvalidModelAttributeValueException('JSON value must decode to an array.');
        }

        return $decoded;
    }

    private function toDateTime(mixed $value): DateTime
    {
        if ($value instanceof DateTime) {
            return clone $value;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTime::createFromInterface($value);
        }

        if (!is_string($value) || trim($value) === '') {
            throw new InvalidModelAttributeValueException('Date-time value must be a non-empty string or DateTimeInterface.');
        }

        return new DateTime($value);
    }

    private function toImmutableDateTime(mixed $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (!is_string($value) || trim($value) === '') {
            throw new InvalidModelAttributeValueException('Immutable date-time value must be a non-empty string or DateTimeInterface.');
        }

        return new DateTimeImmutable($value);
    }
}
