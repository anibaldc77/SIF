---
id: EG-361
title: Controller Architecture and Action Dispatch Model
summary: Defines the governed controller boundary, explicit action references, argument resolution, validation handoff, result normalization and deterministic dispatch model built on the WP-223 HTTP lifecycle.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-02
updated: 2026-08-02
work_package: WP-224
tags:
  - foundation
  - http
  - controller
  - action
  - dispatch
  - validation
  - api
  - architecture
depends_on:
  - EG-360
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Controller Architecture and Action Dispatch Model

WP-224 establishes the governed application-facing controller layer above the transport-neutral HTTP runtime completed by WP-223. The subsystem SHALL convert a matched route and immutable request into an explicitly resolved action invocation, validated input and normalized `ResponseInterface` without moving business logic into the router, transport adapters or middleware pipeline.

## Architectural objective

The accepted request path is:

```text
matched route
    -> controller action reference
    -> argument resolution
    -> input validation
    -> action invocation
    -> result normalization
    -> HTTP response
```

The controller layer SHALL remain optional. Existing handlers that directly implement `RequestHandlerInterface` SHALL continue to work without adopting controllers.

## Scope

WP-224 SHALL define and implement:

- explicit controller and action references;
- deterministic action registration and resolution;
- governed argument descriptions and sources;
- request-input extraction without direct global access;
- validation contracts and structured validation failures;
- controller action invocation;
- normalization of supported action results into `ResponseInterface`;
- API response factories and content negotiation;
- container-backed controller resolution through explicit contracts;
- safe exception mapping and Problem Details-compatible responses;
- application-skeleton templates and a minimal API example;
- compatibility tests and migration guidance.

WP-224 SHALL NOT define authentication, authorization, sessions, CSRF, CORS, rate limiting, form rendering, template rendering, domain validation rules, database transactions, asynchronous controllers or automatic API documentation.

## Layer boundaries

### Router

The router SHALL continue to identify a route and expose route metadata. It SHALL NOT inspect controller methods, resolve dependencies, validate request input or invoke actions.

A route MAY reference a controller action identifier as its handler identifier. The identifier SHALL be resolved by the controller layer through an explicit registry or resolver.

### Controller resolver

The controller resolver SHALL locate a controller instance from a registered action definition. Resolution MAY use the SIF Container through an adapter, but SHALL NOT rely on filesystem scanning, arbitrary class construction or implicit service-locator access inside controllers.

### Argument resolver

The argument resolver SHALL transform request-scoped sources into an ordered action-input map. Supported source classes MAY include:

- route parameters;
- query parameters;
- request attributes;
- parsed request body values;
- headers when explicitly declared;
- cookies when explicitly declared;
- the request object itself;
- the execution context when explicitly declared.

No source SHALL be inferred from untrusted client data merely because a parameter name matches. Route, query, body, header, cookie and service-derived values SHALL remain distinguishable.

### Validator

Validation SHALL occur before action invocation. Validation failures SHALL be represented as structured data with stable codes, field paths and messages. Validators SHALL NOT emit responses directly and SHALL NOT mutate the request.

Domain invariants and persistence constraints remain the responsibility of application services and models. Controller validation SHALL focus on transport-facing input shape and declared constraints.

### Action invoker

The action invoker SHALL execute exactly one resolved action for a matched route. It SHALL receive an immutable invocation description and SHALL return the raw action result to a result normalizer.

The invoker SHALL NOT perform routing, content negotiation, response emission or process termination.

### Result normalizer

The result normalizer SHALL convert only explicitly supported result types into `ResponseInterface`. Initial supported categories MAY include:

- an existing `ResponseInterface`;
- structured API data;
- a governed API result value;
- an empty result with an explicit status.

Arbitrary object serialization, magic conversion and implicit exposure of public properties are prohibited.

## Controller and action model

Controllers SHALL be ordinary application objects. A controller SHALL NOT be required to extend a framework base class.

An action definition SHALL explicitly declare:

- stable action identifier;
- controller service identifier or controller resolver key;
- action method or invokable operation identifier;
- ordered argument definitions;
- accepted result categories;
- optional validation profile;
- optional content-negotiation policy;
- safe diagnostic metadata.

Action identity SHALL not be derived solely from a PHP class name or method name. Registries SHALL reject duplicate action identifiers.

## Reflection policy

WP-224 SHALL NOT use unrestricted reflection-based discovery.

A bounded invoker MAY use reflection only after an action has been explicitly registered and only to verify the declared callable signature. Reflection SHALL NOT scan namespaces, directories, annotations or attributes to discover controllers.

Any reflection use SHALL be deterministic, testable and fail closed when the runtime signature differs from the registered definition.

## Argument resolution

Each action argument SHALL declare a source and a target name. The resolution model SHALL distinguish missing values from explicit `null`.

The following conditions SHALL produce a structured input-resolution failure before invocation:

- required value absent;
- value source not supported;
- duplicate target argument;
- route parameter requested but not present;
- body requested when no compatible parsed representation exists;
- service or context dependency unavailable;
- conversion failure.

Argument conversion SHALL be explicit. Automatic construction of arbitrary objects from client input is prohibited.

## Request input

WP-224 MAY introduce an immutable request-input view that combines explicitly selected route, query and body sources. It SHALL preserve source identity and SHALL not overwrite values silently when the same key exists in multiple sources.

The HTTP body remains bytes at the WP-223 boundary. Parsing JSON or form data SHALL occur through explicit parsers selected by media type. Unsupported or malformed content SHALL produce controlled client errors.

## Validation model

Validation SHALL return a report rather than throw for expected invalid input. A validation issue SHALL contain at least:

- stable machine-readable code;
- field or input path;
- human-readable message;
- optional safe metadata.

Validation reports SHALL be deterministic in issue ordering. Sensitive values SHALL not appear in default issue metadata or logs.

## API responses and content negotiation

API response factories SHALL generate immutable responses and SHALL not emit them.

Content negotiation SHALL be explicit and bounded. The initial implementation MAY support JSON and Problem Details JSON. Unsupported acceptable representations SHALL result in a controlled `406 Not Acceptable` response when negotiation is enabled.

Action results SHALL not control arbitrary response headers unless represented through a governed response value.

## Error model

The controller layer SHALL distinguish:

- action not registered;
- controller resolution failure;
- argument resolution failure;
- malformed request content;
- validation failure;
- unsupported media type;
- unacceptable representation;
- action invocation failure;
- unsupported action result.

Expected client failures SHALL map to stable `4xx` responses. Unexpected failures SHALL flow through the existing HTTP error-handling boundary and SHALL not expose stack traces, service identifiers, filesystem paths or input secrets.

Problem Details-compatible responses SHOULD include stable type identifiers, title, status, detail suitable for the environment and an opaque instance or correlation identifier. Extension members SHALL be governed and safe.

## Container integration

Container integration SHALL be provided through an explicit controller resolver adapter. Controllers MAY receive constructor dependencies from the Container. Action parameters SHALL not become an unrestricted dependency-injection surface.

Request-scoped data SHALL be passed through action arguments or explicit request-scoped services, not registered globally in a mutable singleton container.

## Middleware and context compatibility

Controllers execute after the WP-223 middleware pipeline has reached terminal dispatch. Middleware SHALL remain able to short-circuit before controller execution.

The immutable request delivered to the controller layer SHALL retain:

- route metadata;
- route parameters;
- request attributes;
- execution context;
- middleware-produced safe attributes.

The controller layer SHALL not create a second execution context for the same request.

## Observability

Controller diagnostics MAY include:

- action identifier;
- controller resolver key;
- validation issue count;
- response status;
- execution context and correlation identifiers.

Default diagnostics SHALL exclude body values, headers carrying credentials, cookies, uploaded-file contents and resolved service instances.

## Application skeleton integration

WP-224 SHALL extend application-skeleton templates with an optional API controller example, action registration and validation example. Generated code SHALL use the public controller contracts and SHALL not depend on internal implementation classes unless explicitly intended as a Foundation integration point.

Skeleton updates SHALL preserve the ownership and overwrite rules established by WP-222.

## Compatibility

The controller layer SHALL be additive:

- existing `RequestHandlerInterface` handlers remain valid;
- existing middleware and routing definitions remain valid;
- routes not using controller action identifiers remain unaffected;
- applications without controller runtime composition remain valid;
- direct `ResponseInterface` returns remain supported.

## Security requirements

The subsystem SHALL:

- treat all client-provided values as untrusted;
- preserve source identity during argument resolution;
- reject ambiguous or conflicting input mappings;
- avoid mass assignment into arbitrary objects;
- avoid implicit deserialization of PHP objects;
- cap parser behavior through explicit configuration;
- redact sensitive values from diagnostics;
- fail closed on unknown actions, validators and result types;
- prevent response-header injection through the existing HTTP value model.

## Delivery plan

WP-224 SHALL be delivered in eight increments:

1. controller architecture and action dispatch model;
2. argument resolution and immutable request input;
3. validation contracts, rules and structured failures;
4. API response factories and content negotiation;
5. controller resolver, action registry and Container integration;
6. exception mapping and Problem Details;
7. skeleton templates and minimal API example;
8. compatibility, migration documentation and product completion.

## Acceptance criteria

WP-224 is complete when:

- controller actions are registered and resolved explicitly;
- argument sources remain distinguishable and deterministic;
- expected invalid input does not invoke the action;
- action results normalize through governed contracts;
- Container integration is optional and explicit;
- controller errors map to safe HTTP responses;
- existing direct handlers continue to work;
- skeleton-generated API examples use public contracts;
- PHPUnit, PHPStan level 8 and SIF Builder complete without errors or diagnostics.
