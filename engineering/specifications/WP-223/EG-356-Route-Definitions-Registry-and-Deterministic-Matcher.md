---
id: EG-356
title: Route Definitions Registry and Deterministic Matcher
summary: Defines immutable HTTP route metadata, explicit registration and deterministic path and method matching for the SIF HTTP Foundation.
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
  - routing
  - registry
  - matcher
depends_on:
  - EG-353
  - EG-354
  - EG-355
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Route Definitions, Registry and Deterministic Matcher

WP-223 I4 defines routing as immutable metadata plus deterministic matching. Routing does not instantiate controllers, resolve services or execute handlers.

## Requirements

- Every route MUST have a canonical name, one or more HTTP methods, a normalized path and an explicit handler identifier.
- Route names MUST be unique within a registry.
- Equivalent method and path signatures MUST NOT be registered more than once.
- Path placeholders and declared parameter definitions MUST match exactly.
- Parameter constraints MUST be validated before registration.
- Registry enumeration MUST be deterministic and independent of registration order.
- Matching MUST use the URI path only and MUST NOT execute route handlers.
- A successful match MUST expose decoded parameter values and the matched route definition.
- A path that exists for another method MUST produce a method-not-allowed result with deterministic allowed methods.
- A path with no matching route MUST produce a route-not-found result.
- Route middleware identifiers MUST preserve explicit declaration order.

## Product boundary

This increment does not resolve handlers, execute middleware, create execution contexts, dispatch events or translate route results into HTTP responses. Those concerns remain assigned to later WP-223 increments.
