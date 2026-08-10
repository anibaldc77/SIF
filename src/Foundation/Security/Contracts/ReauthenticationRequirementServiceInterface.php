<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

interface ReauthenticationRequirementServiceInterface
{
    public function requireForSubject(string $subjectReference): void;
}
