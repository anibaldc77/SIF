<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging;

use Sif\Foundation\Logging\Exceptions\InvalidLogRecordException;

final readonly class LogMessage
{
    /** @var list<string> */
    private array $placeholders;

    public function __construct(private string $template)
    {
        if (trim($template) === '') {
            throw InvalidLogRecordException::because('message template must not be empty');
        }

        preg_match_all('/\{([A-Za-z][A-Za-z0-9_.-]*)\}/', $template, $matches);
        /** @var list<string> $placeholders */
        $placeholders = array_values(array_unique($matches[1]));
        $this->placeholders = $placeholders;
    }

    public function template(): string { return $this->template; }

    /** @return list<string> */
    public function placeholders(): array { return $this->placeholders; }

    public function __toString(): string { return $this->template; }
}
