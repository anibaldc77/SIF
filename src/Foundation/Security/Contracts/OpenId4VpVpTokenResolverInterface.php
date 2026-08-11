<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface OpenId4VpVpTokenResolverInterface
{
    /**
     * @param list<string> $vpTokens
     * @return list<mixed>
     */
    public function resolve(array $vpTokens): array;
}
