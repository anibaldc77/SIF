<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\SegregationOfDutiesRule;

interface SegregationOfDutiesRuleProviderInterface
{
    /**
     * @return list<SegregationOfDutiesRule>
     */
    public function all(): array;
}
