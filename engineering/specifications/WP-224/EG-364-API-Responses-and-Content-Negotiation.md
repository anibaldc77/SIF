---
id: EG-364
title: API Responses and Content Negotiation
summary: Specifies explicit API results, deterministic JSON serialization, Accept negotiation and safe 406 and 415 responses for controller actions.
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
  - controller
  - api
  - response
  - content-negotiation
  - json
  - specification
depends_on:
  - EG-361
  - EG-362
  - EG-363
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# API Responses and Content Negotiation

WP-224 I4 defines the representation boundary between explicit controller API results and immutable HTTP responses.

## API results

An `ApiResult` SHALL declare structured data, status, headers and supported media types. Arbitrary objects and resources SHALL NOT be serialized implicitly. JSON serialization SHALL be deterministic for associative keys and SHALL preserve list order.

## Negotiation

`ContentNegotiator` SHALL parse the `Accept` header, quality weights and wildcards. Selection SHALL be deterministic by quality, specificity and declaration order. Missing `Accept` SHALL select the first supported representation.

When no supported representation is acceptable, the factory SHALL return a safe `406 Not Acceptable` response. Unsupported request content types SHALL be representable as safe `415 Unsupported Media Type` responses.

## Media types

The initial representations are `application/json` and `application/problem+json`. Full Problem Details semantics remain assigned to WP-224 I6.

## Security

Serialization failures SHALL not expose object internals or encoder diagnostics to clients. Responses SHALL not infer serialization from public properties, reflection or magic methods.

## Exclusions

I4 does not invoke controllers, map exceptions, validate request input or implement language negotiation.
