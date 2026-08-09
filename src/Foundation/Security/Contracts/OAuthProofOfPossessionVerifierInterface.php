<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPVerificationContext;
use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPVerificationResult;
interface OAuthProofOfPossessionVerifierInterface
{
    public function verify(string $serializedProof, OAuthDPoPVerificationContext $context): OAuthDPoPVerificationResult;
}
