---
id: EG-380
title: Session Transport and Lifecycle Middleware
summary: Specifies cookie-based session transport and request-scoped lifecycle middleware for WP-226 I4.
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
  - middleware
  - lifecycle
  - specification
depends_on:
  - EG-379
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Session Transport and Lifecycle Middleware

WP-226 I4 defines a cookie-based transport and lifecycle middleware over the storage-neutral runtime delivered by I3.

## Transport boundary

`SessionCookieTransport` SHALL read only the configured request cookie and SHALL NOT inspect query, route, body or PHP globals. It SHALL create session and removal cookies through the immutable cookie model from I2. The default configuration SHALL use `__Host-sif_session`, `Secure`, `HttpOnly`, `SameSite=Lax`, `Path=/` and no Domain.

## Middleware lifecycle

`SessionMiddleware` SHALL open exactly one session per request, attach `SessionState` through a stable request attribute, invoke the next handler, commit after a successful response and append a distinct `Set-Cookie` value when a session is created, replaced, regenerated or destroyed.

Handler exceptions SHALL propagate unchanged. In that case the middleware SHALL NOT commit partial state or produce a response cookie.

## Compatibility and boundaries

The middleware SHALL implement `HttpMiddlewareInterface`, remain optional and preserve applications without sessions. It SHALL NOT call `session_start()`, emit headers, terminate the process or store mutable request state in a singleton.
