---
id: EG-353
title: HTTP Foundation Architecture and Request Lifecycle
summary: Defines the transport-neutral HTTP foundation, immutable request and response boundaries, routing, middleware, dispatch, context, error handling and deterministic request lifecycle for SIF applications.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-223
tags:
  - foundation
  - http
  - request
  - response
  - routing
  - middleware
  - lifecycle
  - architecture
depends_on:
  - EG-352
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# HTTP Foundation Architecture and Request Lifecycle

WP-223 establishes the governed HTTP boundary that allows a generated SIF application to accept one request, execute an explicit middleware and dispatch pipeline, and return one response without coupling Foundation contracts to a specific web server, SAPI, framework adapter or global variable.

## Architectural objective

The HTTP subsystem SHALL provide a deterministic path:

```text
HTTP transport adapter
    -> immutable request
    -> request context initialization
    -> router match
    -> middleware pipeline
    -> handler dispatch
    -> immutable response
    -> transport emitter
```

The core HTTP model SHALL remain independent from Apache, Nginx, IIS, PHP-FPM, the PHP development server and any particular PSR implementation. Native process and SAPI access SHALL be confined to adapters.

## Scope

WP-223 SHALL define and implement:

- immutable HTTP request and URI value models;
- normalized method, headers, cookies, query parameters, uploaded-file metadata and body access;
- immutable HTTP responses and response factories;
- route definitions, route collections, matching and parameter extraction;
- middleware contracts and deterministic pipeline execution;
- request-handler and controller-dispatch boundaries;
- request-scoped execution context creation and propagation;
- integration with Container, Event Dispatcher, Logging and Error Handling;
- safe exception-to-response translation;
- HTTP runtime composition and optional application publication;
- native PHP request and response adapters;
- tests and a minimal HTTP example compatible with the application skeleton.

WP-223 SHALL NOT define a template engine, session subsystem, authentication framework, authorization policy engine, CSRF implementation, WebSocket server, asynchronous event loop, reverse proxy, HTTP client or deployment server.

## Layer boundaries

### Transport adapters

Transport adapters MAY read from native process or SAPI sources and MAY emit headers and body content. They SHALL translate transport state into Foundation HTTP contracts and SHALL NOT contain routing, controller or business logic.

### HTTP model

The HTTP model SHALL represent method, URI, protocol version, headers, query values, cookies, server metadata, body and uploaded-file descriptors without reading global state after construction.

The request SHALL be immutable. Any operation that adds an attribute, route parameter or contextual value SHALL return a new request instance or use an explicitly request-scoped attribute container owned by the lifecycle coordinator. Hidden mutation through magic properties is prohibited.

The response SHALL be immutable and SHALL contain status, headers and body representation. Response emission SHALL be separate from response construction.

### Routing

Routing SHALL be deterministic and explicit. Route identity, methods, path pattern, handler reference, middleware references, defaults and name SHALL be declared rather than inferred from controller method names or filesystem scanning.

Route matching SHALL:

- normalize methods consistently;
- preserve decoded and raw path responsibilities explicitly;
- extract named parameters deterministically;
- reject ambiguous duplicate route definitions;
- distinguish not-found from method-not-allowed;
- avoid executing handlers during matching;
- avoid resolving service dependencies during registry construction.

Attribute, annotation and reflection-based route discovery are outside the initial product boundary unless supplied later by an explicit extension.

### Middleware

Middleware SHALL implement a single-request, single-response chain. Registration order SHALL be deterministic. Each middleware SHALL receive the current request and the next handler boundary.

Middleware MAY short-circuit by returning a response. Middleware SHALL NOT invoke the next handler more than once. Re-entrant, concurrent and asynchronous middleware execution are outside the initial boundary.

Global middleware, route middleware and terminal dispatch SHALL compose in an explicit order:

```text
global middleware
    -> matched-route middleware
    -> terminal request handler
```

### Dispatch

A route handler reference SHALL be resolved through an explicit resolver. The router SHALL not instantiate controllers. The dispatcher SHALL validate the resolved handler and SHALL invoke it with governed route parameters and request context.

The initial system SHALL support callable handlers and container-resolved class handlers through explicit adapters. Method inference and unrestricted reflection invocation are prohibited.

## Request lifecycle

A complete lifecycle SHALL contain the following stages:

```text
received
normalized
context-created
matched
middleware-running
dispatched
response-created
emitted
completed
failed
```

The lifecycle coordinator SHALL ensure that:

- exactly one request is processed per invocation;
- a response is produced or an error is translated;
- request-scoped state is released after completion;
- shutdown does not silently emit a second response;
- exceptions are never swallowed;
- lifecycle events and logs do not expose secrets or raw credentials.

## Execution context

Each request SHALL receive an `ExecutionContextInterface` created explicitly from trusted transport and application inputs. Context creation MAY include correlation identifier, tenant, actor, operation and source when available.

Untrusted request headers SHALL NOT automatically become trusted actor or tenant identity. Trusted identity enrichment belongs to later authentication and tenancy middleware.

The request context SHALL be propagated to:

- lifecycle events;
- logging;
- auditing where an application operation emits audit records;
- error handling;
- controller or handler invocation through explicit contracts.

No static current-request or global context accessor SHALL be introduced.

## Error handling

HTTP error translation SHALL reuse the existing Error Handling subsystem. The HTTP layer SHALL classify failures and map an approved error representation to a response without publishing stack traces, credentials, connection details, filesystem paths or internal exception objects.

The default production behavior SHALL be fail-closed and minimal. Development detail MAY be enabled only through explicit environment configuration.

The architecture SHALL distinguish at minimum:

- malformed request;
- route not found;
- method not allowed;
- invalid handler;
- validation failure;
- authorization failure supplied by application middleware;
- unhandled application failure;
- emission failure.

## Events and observability

Lifecycle events MAY be emitted for received, matched, dispatched, completed and failed stages. Events SHALL contain safe identifiers and timing metadata, not full request bodies by default.

Logging SHALL apply existing normalization and redaction policies. Sensitive headers such as authorization and cookies SHALL be redacted by default. Body logging SHALL be disabled by default.

## Request body and streaming boundary

The initial request body abstraction SHALL support deterministic access without assuming that all payloads are JSON. Parsing SHALL be delegated to explicit body parsers or middleware.

Large-body streaming, chunked response streaming and server-sent events are outside the first WP-223 product boundary. The architecture SHALL avoid preventing later stream adapters, but I1 does not authorize hidden buffering or background processing.

## Uploaded files

Uploaded files SHALL be represented by immutable metadata and a controlled temporary-resource reference. The HTTP core SHALL not move files automatically. Validation, persistence and cleanup SHALL be explicit application or middleware responsibilities.

## Security requirements

The subsystem SHALL:

- validate header names and values;
- prevent response-header injection;
- normalize and validate status codes;
- avoid trusting forwarded headers without an explicit trusted-proxy policy;
- reject malformed URI and method values;
- prevent route traversal through path normalization;
- avoid automatic deserialization of arbitrary objects;
- avoid exposing request bodies, cookies or authorization headers in diagnostics;
- emit a response at most once;
- keep native globals outside domain handlers.

Trusted proxy handling, CORS, rate limiting, authentication, authorization, CSRF and sessions SHALL be separate middleware-oriented work packages or extensions.

## Integration with Application Skeleton

The generated `public/index.php` SHALL remain a thin transport entry point. It SHALL load application bootstrap, obtain an HTTP runtime or kernel, translate the native request, execute one lifecycle and emit one response.

WP-223 SHALL not rewrite user-owned application code without a governed generation plan. Skeleton template changes SHALL follow the ownership and overwrite rules defined by WP-222.

## Compatibility

The HTTP foundation SHALL target PHP 8.2 and preserve the existing SIF rules:

- code in English;
- documentation in English for governed engineering artifacts;
- PSR-12-compatible source;
- strict PHPStan level 8 compliance;
- no mandatory namespace changes outside new HTTP components;
- optional runtime integration;
- no breaking public-contract changes without migration guidance.

Interoperability adapters for PSR-7, PSR-15 or external routers MAY be added later. The initial core SHALL not claim standards compliance until the required interfaces and behavioral tests are intentionally implemented.

## Delivery sequence

WP-223 SHALL be delivered in eight governed increments:

```text
I1 - HTTP architecture and request lifecycle
I2 - immutable request, URI, headers and body value model
I3 - immutable response model and native transport adapters
I4 - route definitions, registry and deterministic matcher
I5 - middleware pipeline and handler dispatch
I6 - context, events, logging and error-response integration
I7 - HTTP runtime, native kernel and skeleton example integration
I8 - compatibility, documentation, product completion and closure
```

No increment SHALL execute HTTP requests during bootstrap or introduce implicit global state.

## Acceptance criteria

I1 is accepted when:

- the transport-neutral boundary is explicit;
- request and response immutability is required;
- routing, middleware and dispatch responsibilities are separated;
- context and error-handling integration are defined;
- security and observability defaults are documented;
- application-skeleton integration preserves ownership rules;
- exclusions and future extension points are unambiguous;
- the eight-increment delivery sequence is governed.
