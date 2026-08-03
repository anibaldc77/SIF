---
id: EG-370
title: Route Groups Prefixes and Shared Metadata
summary: Specifies immutable route-group declarations, deterministic nested expansion, path and name prefixes, shared middleware, metadata and defaults while preserving the WP-223 route model.
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
  - route-groups
  - prefixes
  - middleware
  - metadata
  - defaults
  - specification
depends_on:
  - EG-369
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Route Groups, Prefixes and Shared Metadata

## Objective

WP-225 I2 introduces immutable declaration-time route groups above the existing `RouteDefinition` boundary. Group expansion SHALL complete before route registration or request matching.

## Composition order

Nested group state SHALL combine in this order:

```text
parent group -> child group -> route
```

Path prefixes and name prefixes SHALL concatenate deterministically. Shared middleware SHALL preserve parent-first order and SHALL remove only exact duplicate identifiers. Route-specific middleware SHALL execute after inherited middleware.

## Metadata and defaults

Shared metadata and defaults SHALL be immutable, key-addressed value objects. Equivalent repeated values MAY compose. Different values for the same key SHALL fail closed rather than overwrite silently.

Metadata keys SHALL use a portable lowercase identifier grammar. Defaults SHALL use parameter-compatible identifiers and scalar or null values.

## Expansion result

Expansion SHALL produce ordinary `RouteDefinition` instances wrapped with their effective metadata and defaults. Existing route registries and matchers SHALL remain unchanged and SHALL consume the expanded route when advanced metadata is not required.

## Validation

The implementation SHALL reject malformed prefixes, duplicate middleware inside one group, conflicting inherited values and any expansion that produces an invalid route name or path.

## Non-goals

I2 SHALL NOT implement URL generation, host constraints, optional parameters, precedence analysis, route compilation or cache persistence.
