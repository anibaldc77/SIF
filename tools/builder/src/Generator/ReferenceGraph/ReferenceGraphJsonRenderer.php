<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceGraph;

use JsonException;
use RuntimeException;

final class ReferenceGraphJsonRenderer
{
    public const SCHEMA_VERSION = '1.0.0';

    public function render(ReferenceGraphView $view): string
    {
        $document = [
            'schema_version' => self::SCHEMA_VERSION,
            'generated_by' => 'sif-builder',
            'generator' => ReferenceGraphGenerator::IDENTIFIER,
            ...$view->toArray(),
        ];

        try {
            return json_encode(
                $document,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            ) . "\n";
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to encode the reference graph.', 0, $exception);
        }
    }
}
