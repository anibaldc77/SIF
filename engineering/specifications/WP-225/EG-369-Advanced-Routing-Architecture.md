---
id: EG-369
title: Advanced Routing Architecture
summary: Defines the governed architecture for route groups, URL generation, host and scheme constraints, optional parameters, deterministic precedence, compilation, caching and diagnostics above the WP-223 routing foundation.
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
  - url-generation
  - route-groups
  - constraints
  - precedence
  - compilation
  - cache
  - diagnostics
  - architecture
  - specification
depends_on:
  - EG-356
  - EG-359
  - EG-365
  - EG-368
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Advanced Routing Architecture

WP-225 extends the deterministic routing foundation delivered by WP-223 without replacing its public route, registry and match-result boundaries. The advanced routing layer SHALL remain optional, explicit and transport-neutral.

## Architectural objective

The advanced routing engine SHALL support composition, reverse routing and deterministic optimization while preserving the existing lifecycle:

```text
route declarations
    -> route-group expansion
    -> validation and normalization
    -> precedence analysis
    -> compiled route table
    -> deterministic matcher
    -> RouteMatch
```

URL generation SHALL use the same governed route definitions and SHALL NOT depend on request globals or matcher side effects.

## Compatibility boundary

Existing `RouteDefinition`, `RouteRegistry`, `RouteMatcher`, `RouteMatch` and `RouteMatchStatus` behavior SHALL remain compatible. Applications MAY continue registering basic routes directly. Advanced declarations SHALL compile into a representation consumable by the established HTTP lifecycle.

WP-225 SHALL NOT alter middleware execution, handler resolution, controller action invocation, request adaptation or response emission. Those responsibilities remain in WP-223 and WP-224.

## Route groups

Route groups SHALL be immutable declarations that compose shared metadata explicitly. A group MAY define:

- a path prefix;
- a route-name prefix;
- shared middleware identifiers;
- host, scheme or port constraints;
- parameter requirements and defaults;
- arbitrary safe metadata declared through registered contributors.

Nested groups SHALL be flattened deterministically. Parent prefixes and middleware SHALL precede child declarations. Duplicate middleware identifiers SHALL follow an explicit deduplication policy and SHALL NOT be removed by accidental array coercion.

Group expansion SHALL produce ordinary route definitions before matching. The matcher SHALL NOT traverse a mutable group tree during a request.

## Named routes and URL generation

Every route participating in reverse routing SHALL have a unique `RouteName`. URL generation SHALL resolve a route by name and substitute declared parameters only.

Generation SHALL distinguish:

- required path parameters;
- optional path parameters;
- host parameters;
- query parameters not consumed by the route template;
- explicit fragment values.

Missing required parameters, unknown parameters under strict mode, values that violate route constraints and attempts to generate from non-generatable routes SHALL produce structured failures.

URL generation SHALL support relative paths and, when a trusted base URI is supplied explicitly, absolute URLs. It SHALL NOT infer scheme, host or port from untrusted forwarding headers.

## Host, scheme and port constraints

Routes MAY declare constraints for scheme, host and port. Host templates MAY contain governed parameters. Constraint matching SHALL normalize host comparison case-insensitively and SHALL preserve explicit non-default ports.

Trusted-proxy interpretation is outside the route matcher. The matcher receives already-normalized request transport data from an explicit adapter or trusted policy component.

## Optional parameters

Optional parameters SHALL be trailing within a path template unless a future normative extension defines an unambiguous grammar. Optional segments SHALL declare defaults explicitly.

Compilation SHALL reject ambiguous templates, including optional segments that create indistinguishable concrete paths at equal precedence.

A generated URL SHALL omit an optional segment only when its value is absent or equals the declared omission rule. Empty strings SHALL NOT silently mean “missing” unless explicitly configured.

## Deterministic precedence

Route selection SHALL not depend on registration accidents once advanced compilation is enabled. Precedence SHALL be derived from declared specificity and a stable registration ordinal used only as the final tie-breaker.

The initial precedence model SHALL prefer, in order:

1. exact static paths over parameterized paths;
2. constrained parameters over unconstrained parameters;
3. fewer optional segments over more optional segments;
4. more specific host, scheme and port constraints;
5. explicit priority, when permitted by policy;
6. stable declaration order as the final deterministic tie-breaker.

Compilation SHALL detect routes that remain indistinguishable after precedence analysis. Ambiguity SHALL be reported before runtime rather than resolved silently.

## Compilation

A route compiler SHALL transform normalized declarations into an immutable compiled route table. Compilation MAY precompute:

- normalized static-path maps;
- parameterized regular expressions;
- method indexes;
- host and scheme indexes;
- precedence keys;
- reverse-routing templates;
- diagnostic source references;
- a deterministic fingerprint.

Compiled structures SHALL contain data only. They SHALL NOT contain live controller objects, closures, container instances, requests, execution contexts or environment-dependent resources.

## Cache

Route caching SHALL serialize only a versioned compiled representation. A cache artifact SHALL include at least:

- schema version;
- framework compatibility marker;
- compiler version;
- source fingerprint;
- compiled-table fingerprint;
- route count;
- generation timestamp only when excluded from determinism-sensitive hashes.

Loading SHALL fail closed when the schema, compiler, compatibility marker or fingerprints do not match. Cache corruption SHALL never fall back to executing arbitrary serialized objects.

The default cache representation SHALL use safe structured data or generated PHP arrays. Native object serialization is rejected.

## Diagnostics

Compilation SHALL return structured diagnostics for:

- duplicate route names;
- duplicate effective signatures;
- ambiguous precedence;
- invalid group composition;
- missing URL-generation parameters;
- invalid defaults;
- unsupported constraints;
- cache incompatibility;
- routes shadowed by higher-precedence definitions.

Diagnostics SHALL identify routes by stable name and declaration source when available. They SHALL not expose secrets, request bodies, credentials or container state.

## Extension model

Advanced routing SHALL be extended through explicit contracts and registries. Constraint types, metadata contributors, compilers and cache stores SHALL be registered deliberately. Filesystem scanning, annotations, attributes and namespace discovery SHALL remain disabled unless approved by a later governed specification.

## Lifecycle integration

The HTTP lifecycle SHALL continue receiving a matcher-like boundary that returns `RouteMatch`. A compiled matcher MAY replace the basic linear matcher through explicit composition, but SHALL preserve `matched`, `not-found` and `method-not-allowed` semantics.

Handler identifiers and middleware metadata SHALL remain unchanged when a route is compiled. Controller integration SHALL continue through the handler registry created by WP-224.

## Security requirements

The advanced router SHALL:

- validate all templates and constraint patterns before compilation;
- prevent regular-expression delimiter injection and catastrophic user-defined patterns through governed validation policies;
- reject control characters in route names, hosts, paths and generated URLs;
- avoid trusting forwarded host or scheme data by default;
- encode generated path and query values correctly;
- avoid including credentials in generated authorities;
- never evaluate route cache content as untrusted executable input;
- keep diagnostics free of sensitive request data.

## Performance requirements

Optimization SHALL preserve correctness and determinism. Any compiled fast path SHALL produce the same match status, route identity, allowed methods and decoded parameters as the normative declaration model.

Performance benchmarks MAY guide implementation, but no optimization SHALL weaken ambiguity detection, validation, cache integrity or compatibility guarantees.

## Delivery sequence

WP-225 SHALL proceed through:

1. advanced routing architecture;
2. route groups, prefixes and shared metadata;
3. named routes and URL generation;
4. host, scheme and port constraints;
5. optional parameters and deterministic precedence;
6. compilation, cache and diagnostics;
7. CLI, skeleton integration and examples;
8. compatibility, migration documentation and product completion.

## Exclusions

WP-225 does not provide:

- middleware execution;
- controller discovery;
- authentication or authorization;
- trusted-proxy configuration;
- localization negotiation;
- signed URLs;
- rate limiting;
- HTTP server integration;
- deployment-specific rewrite rules.
