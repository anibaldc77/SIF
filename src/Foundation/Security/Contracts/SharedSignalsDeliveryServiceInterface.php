<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SharedSignalsDeliveryEnvelope;
use Sif\Foundation\Security\SharedSignals\SharedSignalsDeliveryResult;

interface SharedSignalsDeliveryServiceInterface
{
    public function deliver(
        SharedSignalsDeliveryEnvelope $envelope
    ): SharedSignalsDeliveryResult;
}
