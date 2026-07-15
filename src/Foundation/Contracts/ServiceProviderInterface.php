<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

interface ServiceProviderInterface
{
    public function register(ApplicationInterface $application): void;

    public function boot(ApplicationInterface $application): void;

    public function shutdown(ApplicationInterface $application): void;
}
