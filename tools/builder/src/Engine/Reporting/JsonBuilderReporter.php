<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Reporting;

use JsonException;
use Sif\Builder\Engine\BuilderResult;
use Sif\Builder\Engine\Contract\ReporterInterface;

final readonly class JsonBuilderReporter implements ReporterInterface
{
    public function id(): string
    {
        return 'report.json';
    }

    public function mediaType(): string
    {
        return 'application/json';
    }

    /** @throws JsonException */
    public function render(BuilderResult $result): string
    {
        return json_encode(
            $result,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ) . PHP_EOL;
    }
}
