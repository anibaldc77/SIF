<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SharedSignalsStreamConfiguration;

interface SharedSignalsDeliveryMethodPolicyInterface
{
    public function validate(
        SharedSignalsStreamConfiguration $configuration
    ): void;
}
