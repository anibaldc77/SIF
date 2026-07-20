<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Diagnostic;

use JsonSerializable;
use Sif\Builder\Engine\Exception\InvalidDiagnosticException;

final readonly class Diagnostic implements JsonSerializable
{
    /** @var array<string, bool|float|int|string|null> */
    public array $context;

    /**
     * @param array<string, bool|float|int|string|null> $context
     */
    public function __construct(
        public string $code,
        public DiagnosticSeverity $severity,
        public string $message,
        public ?string $source = null,
        public ?string $extension = null,
        array $context = [],
        public ?string $remediation = null,
    ) {
        if (!preg_match('/^[A-Z][A-Z0-9]*-[0-9]{3,}$/', $this->code)) {
            throw new InvalidDiagnosticException(sprintf('Diagnostic code "%s" is invalid.', $this->code));
        }

        if (trim($this->message) === '') {
            throw new InvalidDiagnosticException('Diagnostic message must not be empty.');
        }

        foreach ($context as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidDiagnosticException('Diagnostic context keys must be non-empty strings.');
            }

            if (!is_bool($value) && !is_float($value) && !is_int($value) && !is_string($value) && $value !== null) {
                throw new InvalidDiagnosticException(sprintf(
                    'Diagnostic context value for "%s" must be scalar or null.',
                    $key,
                ));
            }
        }

        ksort($context);
        $this->context = $context;
    }

    public function identity(): string
    {
        return implode('|', [
            str_pad((string) (100 - $this->severity->value), 3, '0', STR_PAD_LEFT),
            $this->code,
            $this->source ?? '',
            $this->extension ?? '',
            $this->message,
        ]);
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'severity' => $this->severity->label(),
            'message' => $this->message,
            'source' => $this->source,
            'extension' => $this->extension,
            'context' => $this->context,
            'remediation' => $this->remediation,
        ];
    }
}
