<?php

declare(strict_types=1);

namespace Sif\Foundation\Event\Observation;

/** Runtime operation observed by an explicit kernel decorator. */
enum RuntimeOperation: string
{
    case Boot = 'boot';
    case Run = 'run';
    case Shutdown = 'shutdown';
}
