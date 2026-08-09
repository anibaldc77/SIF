<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
interface OAuthDPoPNonceServiceInterface
{
    public function issue(string $publicKeyThumbprint): string;
    public function validate(string $publicKeyThumbprint, string $nonce): void;
}
