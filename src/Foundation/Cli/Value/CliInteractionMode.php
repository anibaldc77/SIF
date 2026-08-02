<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

final readonly class CliInteractionMode
{
    private function __construct(private bool $interactive)
    {
    }

    public static function interactive(): self { return new self(true); }
    public static function nonInteractive(): self { return new self(false); }
    public function allowsInteraction(): bool { return $this->interactive; }
    public function value(): string { return $this->interactive ? 'interactive' : 'non-interactive'; }
}
