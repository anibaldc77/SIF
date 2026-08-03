---
id: EG-376
title: Advanced Routing Compatibility, Migration and Product Completion
summary: Specifies final compatibility guarantees, incremental adoption, cache safety and product-completion criteria for the optional advanced routing capabilities delivered by WP-225.
status: Draft for Review
version: 0.1.0
category: Normative Specification
document_class: NormativeDocument
authors:
  - SIF Architecture Board
created: 2026-08-03
updated: 2026-08-03
work_package: WP-225
tags:
  - routing
  - compatibility
  - migration
  - completion
  - specification
depends_on:
  - EG-369
  - EG-370
  - EG-371
  - EG-372
  - EG-373
  - EG-374
  - EG-375
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Advanced Routing Compatibility, Migration and Product Completion

WP-225 extends the basic routing model delivered by WP-223 without invalidating `RouteDefinition`, `RouteRegistry`, `RouteMatcher`, `RouteMatch` or existing handler identifiers.

## Compatibility guarantees

Applications SHALL remain able to use the basic linear matcher without composing route groups, URL generation, transport constraints, optional parameters, compilation or cache. Advanced capabilities SHALL remain explicit data components and SHALL NOT discover routes, controllers or middleware automatically.

Matching semantics SHALL preserve `matched`, `not-found` and `method-not-allowed`. Route parameters SHALL remain decoded strings and route identity SHALL remain based on `RouteName`.

## Incremental migration

Applications MAY introduce route groups, named URL generation, constrained matching and compiled tables independently. Existing route definitions MAY be reused directly. Cache SHALL remain disabled unless explicitly configured and cache payloads SHALL be versioned, fingerprinted and rejected on incompatibility or corruption.

## Security and determinism

Absolute URL generation SHALL require an explicit trusted base URI. The router SHALL NOT trust forwarding headers by itself. Compiled artifacts SHALL contain data only and SHALL NOT contain closures, controllers, services, containers, requests, contexts or native serialized objects.

Ambiguous effective routes SHALL fail before runtime. Stable fingerprints, diagnostics and declaration-independent precedence SHALL support reproducible operation.

## Product completion

WP-225 is complete when route groups expand deterministically, named URLs are generated safely, transport constraints are explicit, optional parameters have deterministic precedence, compiled route tables and caches are verified, CLI inspection and user-owned skeleton examples are available, and basic routing remains compatible.
