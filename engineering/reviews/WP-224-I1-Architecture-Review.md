---
id: WP-224-I1-ARCHITECTURE-REVIEW
title: WP-224 I1 Architecture Review
summary: Reviews the controller boundary, explicit action registry, argument-source model, validation handoff, result normalization, Container integration, Problem Details direction and compatibility with the WP-223 HTTP lifecycle.
status: Draft for Review
version: 0.1.0
category: Architecture Review
document_class: ReviewDocument
authors:
  - SIF Team
created: 2026-08-02
updated: 2026-08-02
work_package: WP-224
tags:
  - controller
  - action
  - dispatch
  - validation
  - api
  - http
  - architecture
  - review
depends_on:
  - EG-361
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---

# WP-224 I1 Architecture Review

## Scope reviewed

- boundary between routing, middleware, controller resolution and action invocation;
- explicit controller action registration;
- argument-source identity and deterministic resolution;
- immutable request-input representation;
- transport-facing validation and structured failures;
- action-result normalization;
- API response and content-negotiation direction;
- optional Container-backed controller resolution;
- exception mapping and Problem Details-compatible responses;
- observability and sensitive-data handling;
- application-skeleton integration;
- backward compatibility with direct request handlers.

## Architectural decision

WP-224 SHALL introduce an optional controller layer above the WP-223 terminal dispatch boundary.

The accepted dependency direction is:

```text
route match -> controller action definition -> argument resolution -> validation -> invocation -> result normalization
```

The router SHALL not inspect or instantiate controllers. Middleware SHALL continue to execute before terminal controller dispatch and MAY short-circuit the request.

## Controller decision

Controllers SHALL be ordinary application objects and SHALL not require inheritance from a framework base class. Action identity SHALL be explicit and stable. Discovery through filesystem scanning, annotations or unrestricted reflection is rejected.

## Argument-resolution decision

Every action argument SHALL declare its source. Route, query, body, header, cookie, request, context and service values SHALL not be merged ambiguously. Missing values, explicit `null` and conversion failures SHALL remain distinguishable.

## Validation decision

Expected invalid input SHALL produce deterministic validation reports and SHALL prevent action invocation. Validation SHALL focus on transport-facing input and SHALL not replace domain invariants.

## Result decision

Action results SHALL pass through a governed normalizer. Existing `ResponseInterface` values remain valid. Arbitrary object serialization and implicit public-property exposure are rejected.

## Container decision

Container-backed controller resolution is accepted only through an explicit adapter. The action-argument surface SHALL not become unrestricted dependency injection, and request-scoped values SHALL not be stored in mutable global state.

## Error and API decision

The architecture accepts explicit API response factories, bounded content negotiation and Problem Details-compatible client errors. Unexpected failures remain under the WP-223 error boundary and SHALL not expose internal implementation details.

## Compatibility decision

Direct request handlers, route definitions, middleware and applications without controller composition SHALL remain valid. Controller adoption is additive and route-specific.

## Security decision

Client input is untrusted. Source identity SHALL be preserved, ambiguous mappings SHALL fail closed, mass assignment into arbitrary objects is prohibited and sensitive values SHALL be excluded from default diagnostics.

## Review outcome

The architecture is accepted for implementation in WP-224 I2 through I8. I1 introduces documentation only and does not alter runtime behavior or public PHP contracts.
