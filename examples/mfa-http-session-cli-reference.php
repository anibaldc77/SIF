<?php

declare(strict_types=1);

/*
 * Reference wiring only.
 *
 * Applications opt in by registering:
 * - TotpChallengeResponseEndpoint
 * - RecoveryCodeChallengeResponseEndpoint
 * - MultiFactorSecurityCommandContributor
 *
 * The application remains responsible for:
 * - routes and CSRF policy;
 * - authenticated-session middleware ordering;
 * - authorization of CLI mutation commands;
 * - persistent encrypted TOTP factor storage;
 * - persistent atomic recovery-code storage;
 * - persistent atomic challenge lifecycle storage.
 */
