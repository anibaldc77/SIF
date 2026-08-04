---
id: WP-226-MIGRATION-GUIDE
title: WP-226 Session, Cookie and CSRF Migration Guide
summary: Provides an incremental path for adopting secure cookies, storage-neutral sessions, flash data and CSRF protection in existing SIF HTTP applications.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-226
tags:
  - session
  - cookie
  - csrf
  - migration
  - guide
depends_on:
  - EG-384
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-226 Session, Cookie and CSRF Migration Guide

## 1. Preserve stateless applications

Applications that do not require server-side state do not need to configure WP-226. Existing request handlers, middleware, routes and API responses remain valid.

## 2. Configure the session cookie

Choose an explicit cookie name and transport policy. Production HTTPS deployments SHOULD use `__Host-sif_session`, `Secure`, `HttpOnly`, `SameSite=Lax`, `Path=/` and no `Domain`. Local HTTP development MUST use a different name without the `__Host-` prefix and explicitly disable `Secure` only for that environment.

## 3. Provide a session store

Implement `SessionStoreInterface` or use the in-memory adapter only for tests. The Core does not require PDO, filesystem, Redis or native PHP sessions.

## 4. Compose session runtime and middleware

Create `SessionRuntime` with a store, cryptographic ID generator, clock and policies. Add `SessionMiddleware` only to route groups that need state. Session state becomes available through `SessionRequestAttributes::STATE`.

## 5. Adopt flash and regeneration policies

Use flash data for one-request messages. Configure idle and absolute expiration and an optional regeneration interval. Regenerate after security-sensitive changes. Do not expose identifiers or session contents in logs.

## 6. Add CSRF protection to browser mutations

Compose `CsrfMiddleware` after session middleware for browser routes using POST, PUT, PATCH or DELETE. Render the token from `CsrfTokenManager` into the configured hidden field or send it in the configured header. Do not enable CSRF solely for stateless bearer-token APIs unless their threat model requires it.

## 7. Validate operationally

Run PHPUnit, PHPStan, the SIF Builder, repository validation and `git diff --check`. Confirm that no direct `session_start()`, `$_SESSION`, `$_COOKIE` or header emission was introduced.
