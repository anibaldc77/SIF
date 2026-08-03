---
id: EG-360
title: HTTP Compatibility, Documentation and Product Completion
summary: Specifies compatibility guarantees, product boundaries, validation requirements and completion criteria for the SIF HTTP Foundation and request lifecycle.
authors:
  - SIF Engineering
created: 2026-08-02
updated: 2026-08-02
tags:
  - http
  - compatibility
  - completion
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
work_package: WP-223
depends_on:
  - EG-353
  - EG-354
  - EG-355
  - EG-356
  - EG-357
  - EG-358
  - EG-359
related_adrs: []
---

# HTTP Compatibility, Documentation and Product Completion

## 1. Purpose

This specification closes WP-223 by defining the compatibility guarantees and completion criteria for the HTTP Foundation, request lifecycle, routing, middleware, dispatch, error translation, native transport and runtime integration.

## 2. Compatibility guarantees

- HTTP runtime integration remains optional for existing applications.
- Existing bootstrap flows continue to operate when no `HttpRuntime` is supplied.
- Request and response value objects remain transport-neutral.
- Native globals and output APIs remain confined to native transport adapters.
- Routing does not instantiate or execute handlers during matching.
- Middleware and handlers are resolved explicitly and deterministically.
- Error responses do not expose stack traces, credentials, cookies or authorization data.
- Generated public entry points fail closed when the bootstrap or HTTP runtime is unavailable.

## 3. Product boundaries

WP-223 does not introduce sessions, authentication, authorization, CSRF, CORS, rate limiting, template rendering, WebSockets, an HTTP client, asynchronous servers or deployment configuration.

## 4. Completion criteria

Completion requires:

- immutable request, URI, header, body and uploaded-file values;
- immutable response values and one-shot response emission;
- deterministic routing with distinct not-found and method-not-allowed results;
- explicit middleware and handler resolution;
- request-scoped context, lifecycle events and safe logging metadata;
- deterministic error responses;
- optional HTTP runtime publication through `Application` and `Bootstrap`;
- generated skeleton delegation to the configured runtime;
- compatibility tests for applications without HTTP integration;
- Composer validation, complete PHPUnit execution, PHPStan level 8, governed artifact generation with zero diagnostics and a clean diff check.

## 5. Migration policy

Applications may adopt WP-223 incrementally. They can first use request and response values in tests, then register routes and handlers, then compose middleware and lifecycle services, and finally provide an `HttpRuntime` to `Bootstrap`. No application is required to enable native transport merely because the HTTP classes are present.
