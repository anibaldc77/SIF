<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

/** Stable machine-readable codes for isolated observation diagnostics. */
enum ObservationDiagnosticCode: string
{
    case ListenerFailure = 'OBSERVATION-001';
}
