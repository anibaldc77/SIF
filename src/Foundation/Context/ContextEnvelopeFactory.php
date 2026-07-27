<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextCarrierInterface;

/** Creates context envelopes from explicit carriers and scopes. */
final class ContextEnvelopeFactory
{
    public static function fromCarrier(object $payload, ContextCarrierInterface $carrier): ContextEnvelope
    {
        return new ContextEnvelope($payload, $carrier->context());
    }
}
