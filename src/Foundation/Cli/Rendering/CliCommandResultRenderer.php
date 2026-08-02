<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Rendering;

use JsonException;
use Sif\Foundation\Cli\Contracts\CliOutputInterface;
use Sif\Foundation\Cli\Exceptions\CliConsoleException;
use Sif\Foundation\Cli\Value\CliCommandResult;

final readonly class CliCommandResultRenderer
{
    public function __construct(private CliOutputFormat $format)
    {
    }

    public function render(CliCommandResult $result, CliOutputInterface $output): void
    {
        $content = $this->format->value() === 'json'
            ? $this->renderJson($result)
            : $this->renderText($result);

        if ($result->exitCode()->successful()) {
            $output->write($content);
            return;
        }

        $output->writeError($content);
    }

    private function renderText(CliCommandResult $result): string
    {
        $lines = [];
        if ($result->message() !== null) {
            $lines[] = $result->message();
        }

        foreach ($result->warnings() as $warning) {
            $lines[] = sprintf('WARNING: %s', $warning);
        }

        if ($result->data() !== []) {
            try {
                $lines[] = json_encode($result->data(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (JsonException $exception) {
                throw new CliConsoleException('CLI result data could not be encoded.', 0, $exception);
            }
        }

        if ($lines === []) {
            $lines[] = $result->exitCode()->label();
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function renderJson(CliCommandResult $result): string
    {
        try {
            return json_encode([
                'exit_code' => $result->exitCode()->value(),
                'status' => $result->exitCode()->label(),
                'message' => $result->message(),
                'data' => $result->data(),
                'warnings' => $result->warnings(),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
        } catch (JsonException $exception) {
            throw new CliConsoleException('CLI result could not be encoded as JSON.', 0, $exception);
        }
    }
}
