<?php

declare(strict_types=1);

use Sif\Foundation\Security\Http\Password\PasswordLoginEndpoint;
use Sif\Foundation\Security\Http\Password\PasswordLogoutEndpoint;

// Application Skeleton wiring reference:
// POST /login  -> PasswordLoginEndpoint::handle($request, $session, $clock->now())
// POST /logout -> PasswordLogoutEndpoint::handle($session)
//
// The application remains responsible for route registration, CSRF policy,
// session middleware ordering and concrete identity/hash providers.
