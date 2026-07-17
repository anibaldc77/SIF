<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

use Sif\Builder\Metadata\Exception\MetadataReadException;

final class MarkdownFrontMatterReader implements MetadataReaderInterface
{
    public function supports(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'md';
    }

    public function read(string $path): MetadataDocument
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            throw MetadataReadException::unreadable($path);
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);
        if (($lines[0] ?? null) !== '---') {
            throw MetadataReadException::missingFrontMatter($path);
        }

        $metadata = [];
        $activeList = null;
        $closed = false;

        for ($index = 1, $count = count($lines); $index < $count; $index++) {
            $line = $lines[$index];
            $lineNumber = $index + 1;

            if ($line === '---') {
                $closed = true;
                break;
            }

            if (trim($line) === '' || str_starts_with(ltrim($line), '#')) {
                continue;
            }

            if (preg_match('/^\s+-\s+(.+)$/', $line, $matches) === 1) {
                if ($activeList === null) {
                    throw MetadataReadException::malformed($path, $lineNumber, 'list item has no parent key');
                }
                /** @var list<mixed> $list */
                $list = $metadata[$activeList];
                $list[] = $this->parseScalar(trim($matches[1]));
                $metadata[$activeList] = $list;
                continue;
            }

            if (preg_match('/^([A-Za-z][A-Za-z0-9_]*):(?:\s*(.*))$/', $line, $matches) !== 1) {
                throw MetadataReadException::malformed($path, $lineNumber, 'expected a top-level key/value pair');
            }

            $key = $matches[1];
            $rawValue = trim($matches[2]);
            if (array_key_exists($key, $metadata)) {
                throw MetadataReadException::malformed($path, $lineNumber, sprintf('duplicate key "%s"', $key));
            }

            if ($rawValue === '') {
                $metadata[$key] = [];
                $activeList = $key;
                continue;
            }

            $metadata[$key] = $this->parseValue($rawValue, $path, $lineNumber);
            $activeList = null;
        }

        if (!$closed) {
            throw MetadataReadException::malformed($path, count($lines), 'closing delimiter is missing');
        }

        return new MetadataDocument($path, $metadata);
    }

    private function parseValue(string $value, string $path, int $line): mixed
    {
        if (str_starts_with($value, '[')) {
            if (!str_ends_with($value, ']')) {
                throw MetadataReadException::malformed($path, $line, 'inline list is not closed');
            }

            $inner = trim(substr($value, 1, -1));
            if ($inner === '') {
                return [];
            }

            return array_map(
                fn (string $item): mixed => $this->parseScalar(trim($item)),
                explode(',', $inner),
            );
        }

        return $this->parseScalar($value);
    }

    private function parseScalar(string $value): mixed
    {
        $length = strlen($value);
        if ($length >= 2) {
            $first = $value[0];
            $last = $value[$length - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, -1);
            }
        }

        return match (strtolower($value)) {
            'null', '~' => null,
            'true' => true,
            'false' => false,
            default => $value,
        };
    }
}
