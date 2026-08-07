<?php

declare(strict_types=1);

/*
 * Reference wiring only.
 *
 * Applications may opt in to:
 * - AdvancedAuthorizationService;
 * - AdvancedAuthorizationGuard;
 * - ControllerAuthorizationBridge;
 * - AdvancedAuthorizationDiagnosticService;
 * - AdvancedAuthorizationSecurityCommandContributor.
 *
 * Recommended controller flow:
 *
 * 1. obtain the already-authenticated principal;
 * 2. construct resource/environment AuthorizationAttributeBag values;
 * 3. create AdvancedAuthorizationRequest;
 * 4. obtain the canonical AuthorizationDecision;
 * 5. map denied decisions to the application's normal 403/404 strategy.
 *
 * SIF intentionally does not:
 * - register routes automatically;
 * - choose 403 versus 404;
 * - expose resource attributes in diagnostics;
 * - cache ABAC decisions;
 * - mutate authentication state during authorization.
 */
