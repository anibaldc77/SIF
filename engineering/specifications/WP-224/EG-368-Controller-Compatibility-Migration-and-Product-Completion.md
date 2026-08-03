---
id: EG-368
title: Controller Compatibility, Migration and Product Completion
summary: Specifies the final compatibility guarantees, migration path, public contracts and product-completion criteria for the optional SIF controller, validation and API response layer.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-224
tags:
  - controller
  - validation
  - api
  - compatibility
  - migration
  - completion
  - specification
depends_on:
  - EG-361
  - EG-362
  - EG-363
  - EG-364
  - EG-365
  - EG-366
  - EG-367
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Controller Compatibility, Migration and Product Completion

WP-224 I8 closes the optional controller, validation and API-response layer while preserving all transport-neutral HTTP handlers delivered by WP-223.

## Compatibility guarantees

Applications SHALL remain able to register and execute `RequestHandlerInterface` implementations without composing controller actions. Controller action discovery SHALL remain disabled unless definitions are registered explicitly.

The controller layer SHALL preserve the public boundaries established by WP-223: routing identifies handlers, middleware remains transport-neutral, responses are emitted only by transport adapters and request-scoped data SHALL NOT be stored in global mutable state.

## Controller adoption

Controller adoption SHALL be incremental. An application MAY migrate one handler at a time by registering a controller service, an action definition and the resulting request handler. Existing route identifiers and middleware ordering MAY remain unchanged during that migration.

Argument sources SHALL remain explicit. Request input, validation, API result normalization and exception mapping SHALL remain separate stages. Applications SHALL NOT rely on namespace scanning, attributes, annotations, automatic controller construction or unrestricted type-driven service injection.

## API and error guarantees

`ApiResult` SHALL remain transport-neutral until normalized by an API response factory. JSON serialization SHALL accept structured values only. Unsupported representations SHALL produce safe negotiation responses.

Problem Details responses SHALL use `application/problem+json`, SHALL expose only declared safe fields and SHALL NOT publish stack traces, filesystem paths, credentials, internal exception messages or container internals. Unexpected failures SHALL use a generic `500` representation and MAY include only an opaque failure identifier produced by the error-handling boundary.

## Skeleton guarantees

Controller API skeleton artifacts SHALL remain user-owned and fail on conflict. Generated examples SHALL use explicit route, handler, controller-service and action identifiers. Skeleton generation SHALL NOT execute Composer, run migrations, start a server or load generated application code.

## Product-completion criteria

WP-224 is complete when controller actions are explicitly registered, arguments are resolved from governed sources, validation failures are structured, API content negotiation is deterministic, controllers resolve through the Container adapter, Problem Details responses are safe, skeleton templates are deterministic and existing HTTP handlers remain compatible.
