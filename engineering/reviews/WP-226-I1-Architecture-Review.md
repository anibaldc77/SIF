---
id: WP-226-I1-REVIEW
title: WP-226 I1 Architecture Review
summary: Reviews the proposed session, cookie and CSRF security architecture, including lifecycle, storage neutrality, expiration, regeneration, flash data and compatibility boundaries.
status: Draft for Review
version: 1.0.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-03
updated: 2026-08-03
work_package: WP-226
increment: I1
tags:
  - session
  - cookie
  - csrf
  - security
  - http
  - architecture
  - review
depends_on:
  - EG-377
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-226 I1 Architecture Review

## Review scope

This review evaluates EG-377 as the governing architecture for WP-226.

## Findings

### Boundary separation

**Accepted.** Cookie serialization, logical session state, storage, transport, lifecycle middleware, flash data and CSRF are separated into explicit responsibilities.

### Storage neutrality

**Accepted.** The architecture does not bind the foundation to native PHP sessions, PDO, filesystem or a specific distributed cache.

### Security posture

**Accepted.** The design requires opaque identifiers, secure cookie invariants, fixation resistance, constant-time CSRF comparison and strict logging exclusions.

### HTTP integration

**Accepted.** Cookie emission remains an immutable response transformation. Native header APIs remain confined to the existing response emitter.

### Compatibility

**Accepted.** Session and CSRF capabilities are opt-in and do not change existing stateless applications.

### Scope control

**Accepted.** Authentication, authorization, identities, roles, OAuth and JWT remain outside WP-226.

## Risks and required controls

1. Storage implementations must define concurrency behavior rather than silently assuming last-write-wins.
2. Session identifiers and CSRF tokens must never appear in logs or diagnostics.
3. Cookie prefix rules must be validated during construction.
4. Expiration tests must use an injected clock.
5. CSRF middleware must not perform implicit body parsing.
6. Regeneration must not lose logical state or leave both identifiers active indefinitely.

## Decision

**Approved for implementation planning.**

WP-226 may proceed to I2 under the architecture and constraints defined by EG-377.
