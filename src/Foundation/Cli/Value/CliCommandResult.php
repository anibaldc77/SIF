<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliCommandResultException;

final readonly class CliCommandResult
{
    /** @var array<string, mixed> */
    private array $data;

    /** @var list<string> */
    private array $warnings;

    /**
     * @param array<string, mixed> $data
     * @param list<string> $warnings
     */
    public function __construct(
        private CliExitCode $exitCode,
        private ?string $message = null,
        array $data = [],
        array $warnings = [],
    ) {
        if ($this->message !== null && trim($this->message) === '') {
            throw new InvalidCliCommandResultException('A CLI command result message cannot be blank.');
        }

        foreach ($warnings as $warning) {
            if (trim($warning) === '') {
                throw new InvalidCliCommandResultException('CLI command warnings cannot be blank.');
            }
        }

        $this->data = $data;
        $this->warnings = array_values($warnings);
    }

    public static function success(?string $message = null): self
    {
        return new self(CliExitCode::success(), $message);
    }

    public function exitCode(): CliExitCode { return $this->exitCode; }
    public function message(): ?string { return $this->message; }
    /** @return array<string, mixed> */ public function data(): array { return $this->data; }
    /** @return list<string> */ public function warnings(): array { return $this->warnings; }

    /** @return array{exit_code: int, status: string, has_message: bool, data_keys: list<string>, warning_count: int} */
    public function safeSummary(): array
    {
        return [
            'exit_code' => $this->exitCode->value(),
            'status' => $this->exitCode->label(),
            'has_message' => $this->message !== null,
            'data_keys' => array_keys($this->data),
            'warning_count' => count($this->warnings),
        ];
    }
}
