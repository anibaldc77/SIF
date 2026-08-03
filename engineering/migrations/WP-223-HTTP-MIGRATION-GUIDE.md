---
id: WP-223-HTTP-MIGRATION-GUIDE
title: HTTP Foundation Migration Guide
summary: Guides applications from direct PHP globals and response emission to the SIF HTTP request lifecycle, routing, middleware and runtime composition.
status: Draft for Review
version: 0.1.0
category: Informative Document
document_class: InformativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-223
tags:
  - http
  - migration
  - compatibility
depends_on:
  - EG-360
related_adrs: []
supersedes: null
superseded_by: null
---

# HTTP Foundation Migration Guide

## 1. Adopt immutable HTTP values

Replace direct reads from `$_SERVER`, `$_GET`, `$_COOKIE`, `$_FILES` and `php://input` inside application code with `RequestInterface` values supplied by the lifecycle. Replace direct `header()` and output calls with immutable `ResponseInterface` values.

## 2. Register routes explicitly

Declare each route with a stable name, supported methods, path, handler identifier, parameters and middleware identifiers. Avoid reflection-based discovery during migration.

## 3. Register handlers and middleware

Register handler and middleware instances in their explicit registries. Keep domain behavior outside the router and transport adapters.

## 4. Compose the request lifecycle

Provide a route matcher, handler dispatcher, context factory and error handler to `HttpRequestLifecycleCoordinator`. Verify not-found, method-not-allowed and unexpected-failure responses before enabling native transport.

## 5. Enable the HTTP runtime

Wrap the lifecycle in `NativeHttpKernel` and `HttpRuntime`, then provide the runtime to `Bootstrap`. Existing applications may omit this step and continue without HTTP capabilities.

## 6. Delegate from the public entry point

Keep `public/index.php` thin: load the bootstrap, create the application, obtain its HTTP runtime and execute one native lifecycle through a response emitter.

## 7. Compatibility checklist

- no application service reads HTTP globals directly;
- no handler emits headers or body directly;
- route names and handler identifiers are stable;
- middleware order is explicit;
- request context is created per request;
- authorization, cookies and bodies are not logged by default;
- error responses are safe for production;
- bootstrap does not process requests automatically.
