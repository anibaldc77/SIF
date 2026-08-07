<?php

declare(strict_types=1);

/*
 * Reference wiring only.
 *
 * Applications may opt in to:
 * - PersistentAuthenticationHttpRestorationService
 * - PersistentAuthenticationSecurityCommandContributor
 *
 * The application remains responsible for:
 * - cookie name;
 * - Secure / HttpOnly;
 * - SameSite;
 * - domain and path;
 * - absolute Max-Age / Expires policy;
 * - CSRF protection for state-changing endpoints;
 * - authorization of administrative CLI commands;
 * - persistent atomic credential storage.
 *
 * A successful restoration must write the replacement cookie returned by
 * replacementCookie(). Reusing the old cookie is expected to trigger
 * replay detection and revocation.
 */
