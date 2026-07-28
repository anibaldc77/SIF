<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Rendering;

use Sif\Foundation\Logging\Contracts\CanonicalStructuredValueSerializerInterface;
use Sif\Foundation\Logging\Contracts\LogMessageRendererInterface;
use Sif\Foundation\Logging\LogRecord;

final readonly class PlaceholderMessageRenderer implements LogMessageRendererInterface
{
    public function __construct(private CanonicalStructuredValueSerializerInterface $serializer)
    {
    }

    public function render(LogRecord $record): RenderedLogMessage
    {
        $template = $record->message()->template();
        $rendered = $template;
        $unresolved = [];

        foreach ($record->message()->placeholders() as $placeholder) {
            [$found, $value] = $this->findValue($record->attributes(), $placeholder);
            if (!$found) {
                $unresolved[] = $placeholder;
                continue;
            }

            $rendered = str_replace(
                '{' . $placeholder . '}',
                $this->renderValue($value),
                $rendered,
            );
        }

        return new RenderedLogMessage($template, $rendered, $unresolved);
    }

    /**
     * @param array<string, null|bool|int|float|string|array<mixed>> $attributes
     * @return array{bool, mixed}
     */
    private function findValue(array $attributes, string $path): array
    {
        $current = $attributes;
        $segments = explode('.', $path);

        foreach ($segments as $index => $segment) {
            if (!array_key_exists($segment, $current)) {
                return [false, null];
            }
            $value = $current[$segment];
            if ($index === count($segments) - 1) {
                return [true, $value];
            }
            if (!is_array($value)) {
                return [false, null];
            }
            $current = $value;
        }

        return [false, null];
    }

    private function renderValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_string($value) || is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            return $this->serializer->serialize($value);
        }
        if (is_array($value)) {
            return $this->serializer->serialize($value);
        }

        return '[unsupported]';
    }
}
