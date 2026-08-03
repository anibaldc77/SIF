---
id: WP-225-I1-ARCHITECTURE-REVIEW
title: WP-225 I1 Architecture Review
summary: Reviews the advanced routing boundaries for group expansion, reverse routing, transport constraints, optional parameters, deterministic precedence, compilation, safe caching, diagnostics and compatibility with WP-223 and WP-224.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-03
updated: 2026-08-03
work_package: WP-225
tags:
  - routing
  - groups
  - url-generation
  - constraints
  - precedence
  - compilation
  - cache
  - diagnostics
  - architecture
  - review
depends_on:
  - EG-369
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# WP-225 I1 Architecture Review

## Scope reviewed

- compatibility with the WP-223 routing foundation;
- route-group composition and flattening;
- named routes and reverse URL generation;
- host, scheme and port constraints;
- optional path segments and defaults;
- deterministic route precedence;
- route compilation and immutable compiled tables;
- safe cache formats and integrity checks;
- ambiguity, shadowing and cache diagnostics;
- extension contracts and security requirements;
- integration with WP-224 controller handlers.

## Architectural decision

WP-225 SHALL extend rather than replace the public routing boundaries delivered by WP-223. Basic route registration SHALL remain valid. Advanced declarations SHALL normalize and compile into an immutable table that preserves `RouteMatch` semantics.

The accepted dependency direction is:

```text
advanced declarations -> normalization -> compilation -> matcher -> HTTP lifecycle
```

Reverse routing SHALL use the same normalized declarations but SHALL remain independent of request processing.

## Group decision

Route groups are accepted as immutable declaration-time composition. Groups SHALL be flattened before request matching. Prefixes, middleware and constraints SHALL combine through explicit rules, and invalid or ambiguous compositions SHALL fail during validation or compilation.

The runtime matcher SHALL not traverse mutable group state.

## URL-generation decision

Named-route URL generation is accepted. Required, optional, host and residual query parameters SHALL remain distinguishable. Absolute URL generation SHALL require an explicit trusted base URI and SHALL not derive authority from untrusted forwarding headers.

Unknown or constraint-violating parameters SHALL produce structured failures rather than malformed URLs.

## Constraint decision

Scheme, host and port constraints are accepted as routing metadata. Trusted-proxy interpretation remains outside routing. Host matching SHALL be case-insensitive, while generated authorities SHALL preserve valid explicit ports.

## Optional-parameter decision

Optional path parameters are accepted only under an unambiguous trailing-segment grammar for the initial implementation. Defaults SHALL be explicit. Compilation SHALL reject templates whose expanded forms cannot be distinguished deterministically.

## Precedence decision

Advanced routing SHALL not rely solely on registration order. Specificity SHALL determine precedence, with stable declaration order used only as the final tie-breaker. Indistinguishable routes SHALL be reported as ambiguity instead of silently shadowing one another.

## Compilation decision

Compilation is accepted as an optimization and validation boundary. Compiled tables SHALL contain immutable data and SHALL preserve the normative matching result. Live services, closures, requests, contexts and controller instances are prohibited from compiled artifacts.

## Cache decision

Route cache artifacts SHALL use safe, versioned structured data or generated PHP arrays. Native object serialization is rejected. Schema, compiler and fingerprint mismatches SHALL fail closed.

## Diagnostics decision

The compiler SHALL report duplicates, ambiguity, invalid defaults, unsupported constraints, shadowing and cache incompatibility through structured diagnostics. Diagnostic content SHALL identify routes without exposing sensitive runtime data.

## Compatibility decision

`RouteDefinition`, `RouteRegistry`, `RouteMatcher`, `RouteMatch` and direct `RequestHandlerInterface` usage SHALL remain supported. The advanced matcher MAY be composed explicitly in place of the linear matcher but SHALL preserve status and parameter semantics.

Controller actions remain handler identifiers resolved through the WP-224 handler registry. WP-225 SHALL not duplicate controller resolution or middleware execution.

## Security review

The architecture rejects:

- automatic route discovery;
- unvalidated regular expressions;
- trust of forwarded host or scheme headers by default;
- executable serialized cache objects;
- credentials embedded in generated authorities;
- cache loading without compatibility and integrity checks;
- diagnostics containing request bodies, authorization data or secrets.

## Review conclusion

The architecture is accepted for implementation. I2 may introduce immutable route groups, prefix composition and shared metadata provided the existing basic routing API remains compatible and all group expansion is deterministic.
