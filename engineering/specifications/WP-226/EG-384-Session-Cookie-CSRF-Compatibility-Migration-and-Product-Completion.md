---
id: EG-384
title: Session, Cookie and CSRF Compatibility, Migration and Product Completion
summary: Specifies compatibility guarantees, incremental adoption, security boundaries and completion criteria for the session, cookie and CSRF foundation delivered by WP-226.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-226
tags:
  - session
  - cookie
  - csrf
  - compatibility
  - migration
  - completion
  - specification
depends_on:
  - EG-377
  - EG-378
  - EG-379
  - EG-380
  - EG-381
  - EG-382
  - EG-383
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Session, Cookie and CSRF Compatibility, Migration and Product Completion

WP-226 adds optional stateful web-security capabilities without invalidating stateless HTTP applications or changing the public request, response, routing, middleware and controller contracts delivered by earlier Work Packages.

## Compatibility guarantees

Applications SHALL remain able to execute HTTP handlers without session or CSRF middleware. Session support SHALL be enabled only through explicit composition of a `SessionStoreInterface`, `SessionRuntime`, cookie transport and `SessionMiddleware`. CSRF protection SHALL be enabled only through explicit middleware composition.

The implementation SHALL NOT require `session_start()`, `$_SESSION`, `$_COOKIE`, direct header emission or process termination. Authentication, authorization, users, roles, permissions, OAuth, OpenID Connect and JWT remain outside WP-226.

## Security guarantees

Cookie names and values SHALL be validated before serialization. `SameSite=None` SHALL require `Secure`; `__Secure-` and `__Host-` invariants SHALL fail closed. Session identifiers and CSRF tokens SHALL be cryptographically random, opaque and excluded from public responses, logs and diagnostics.

Session IDs SHALL be accepted only from the configured cookie transport. Regeneration SHALL invalidate the prior identifier. Expired and destroyed sessions SHALL not restore prior data. CSRF comparison SHALL use constant-time comparison and protected failures SHALL return a generic `403 application/problem+json` response.

## Incremental migration

Applications MAY adopt cookies, sessions, flash data, regeneration policies and CSRF independently. Stateless APIs MAY remain without session and CSRF. Stateful browser routes MAY compose session middleware first, then opt into CSRF for mutable methods. Existing HTTP handlers do not require rewriting solely to complete this migration.

## Product completion

WP-226 is complete when immutable cookies serialize deterministically, session storage remains neutral, lifecycle middleware persists and removes sessions correctly, flash transitions and expiration are deterministic, CSRF protects mutable methods without exposing secrets, CLI and skeleton examples are available, and compatibility tests confirm opt-in adoption.
