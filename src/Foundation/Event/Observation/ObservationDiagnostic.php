<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

use JsonSerializable;

/** Stable serializable representation of an observation failure. */
final readonly class ObservationDiagnostic implements JsonSerializable
{
    public function __construct(
        private ObservationDiagnosticCode $code,
        private ObservationFailure $failure,
    ) {
    }

    public static function fromFailure(ObservationFailure $failure): self
    {
        return new self(ObservationDiagnosticCode::ListenerFailure, $failure);
    }

    public function code(): ObservationDiagnosticCode
    {
        return $this->code;
    }

    public function failure(): ObservationFailure
    {
        return $this->failure;
    }

    /**
     * @return array{
     *   code: non-empty-string,
     *   event_type: class-string,
     *   cause_type: class-string<\Throwable>,
     *   message: string,
     *   occurred_at: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code->value,
            ...$this->failure->jsonSerialize(),
        ];
    }
}
