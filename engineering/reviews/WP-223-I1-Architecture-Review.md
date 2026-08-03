---
id: WP-223-I1-ARCHITECTURE-REVIEW
title: WP-223 I1 Architecture Review
summary: Reviews the transport-neutral HTTP boundary, immutable request and response model, deterministic routing, middleware, dispatch, context, observability, error translation and application-skeleton integration.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-02
updated: 2026-08-02
work_package: WP-223
tags:
  - http
  - request
  - response
  - routing
  - middleware
  - lifecycle
  - architecture
  - review
depends_on:
  - EG-353
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-223 I1 Architecture Review

## Scope reviewed

- transport adapters and native SAPI isolation;
- immutable request, URI, headers, body and uploaded-file boundaries;
- immutable response construction and separate emission;
- deterministic route definitions, registry and matching;
- middleware ordering and short-circuit behavior;
- handler resolution and dispatch;
- request-scoped execution context;
- lifecycle stages and cleanup;
- integration with Error Handling, Event, Logging and Container;
- secure diagnostic and production error behavior;
- trusted proxy and sensitive-header boundaries;
- application-skeleton HTTP entry point;
- compatibility and eight-increment delivery plan.

## Architectural decision

WP-223 SHALL introduce HTTP as an optional adapter-oriented Foundation subsystem.

The accepted dependency direction is:

```text
native transport -> HTTP contracts and runtime -> application handlers
```

Application handlers SHALL NOT depend on `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`, `php://input`, `header()` or direct process termination.

## Request and response decision

Requests and responses SHALL be immutable value-oriented objects. Route matching and middleware SHALL not mutate native global state. Response creation and response emission SHALL remain separate concerns.

## Routing decision

Routes SHALL be registered explicitly. The initial router SHALL not discover routes through reflection, annotations, attributes or filesystem scanning. Matching SHALL be deterministic and SHALL distinguish route absence from method mismatch.

## Middleware decision

Middleware SHALL execute in a single ordered chain and MAY short-circuit by returning a response. The architecture SHALL prevent hidden double dispatch and SHALL not claim asynchronous middleware support.

## Context and error decision

Each request SHALL receive an explicit execution context. Error translation SHALL reuse the existing governed Error Handling subsystem and SHALL produce safe responses without exposing stack traces or sensitive request data in production.

## Security decision

Forwarded headers SHALL be untrusted unless a later explicit trusted-proxy policy accepts them. Authorization headers, cookies and request bodies SHALL be excluded from default diagnostics. Header injection and malformed URI inputs SHALL be rejected.

## Skeleton integration decision

`public/index.php` SHALL remain a thin adapter generated under WP-222 ownership rules. HTTP runtime composition SHALL be optional and SHALL not process a request during Bootstrap registration.

## Exclusions confirmed

I1 does not authorize implementation of:

- sessions;
- authentication or authorization policy;
- CSRF;
- CORS;
- rate limiting;
- template rendering;
- WebSockets;
- asynchronous servers;
- HTTP clients;
- streaming responses;
- deployment configuration.

These concerns require explicit later specifications.

## Delivery decision

The architecture is approved for implementation in eight increments:

```text
I1 - architecture and lifecycle
I2 - request value model
I3 - response and native transport
I4 - routing
I5 - middleware and dispatch
I6 - context, events, logging and errors
I7 - runtime and skeleton integration
I8 - compatibility and completion
```

## Review outcome

The architecture is internally consistent with the existing SIF Runtime, Container, Event, Context, Error Handling, Logging and Application Skeleton subsystems. I1 may proceed to governed repository validation without introducing executable HTTP behavior.
