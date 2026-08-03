---
id: EG-362
title: Argument Resolution and Immutable Request Input
summary: Specifies explicit action-argument sources, immutable request-input provenance, safe scalar conversion, governed body parsing and isolated service resolution for the WP-224 controller layer.
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
  - argument
  - input
  - request
  - resolution
  - conversion
  - json
  - specification
depends_on:
  - EG-361
related_adrs:
  - ADR-0005
supersedes: null
superseded_by: null
---
# Argument Resolution and Immutable Request Input

WP-224 I2 defines the transport-facing argument-resolution boundary between the immutable WP-223 request model and later controller-action invocation. Resolution SHALL be deterministic, source-aware and free of controller execution.

## Argument definitions

Every argument SHALL declare a stable argument name, exactly one source, a governed target type and optional source key. Supported sources are route, query, body, header, cookie, request attribute, request object, execution context and explicit service resolution.

Request, context and service sources SHALL use their corresponding governed types. Services SHALL be resolved only through `ActionServiceResolverInterface`; arbitrary Container lookups from request values are prohibited.

## Immutable request input

`RequestInput` SHALL preserve values by source and SHALL distinguish absence from explicit `null`. Route parameters SHALL be read from the request-scoped `route.parameters` attribute established by WP-223. Header lookup SHALL be case-insensitive.

No precedence chain SHALL merge route, query, body or cookie values. An argument resolves only from its declared source.

## Body parsing

Request bodies remain bytes at the HTTP boundary. Parsing SHALL occur through `RequestBodyParserInterface`. I2 supplies an explicit JSON parser for `application/json` and structured `+json` media types. The parser SHALL accept only a JSON object at the document root and SHALL not deserialize arbitrary PHP objects.

Unsupported media types and parse failures SHALL become safe structured issues.

## Conversion

Governed conversion is limited to string, integer, float, boolean, array and mixed values. Missing required values, disallowed nulls and conversion failures SHALL be represented as `ActionArgumentIssue` values. Expected invalid input SHALL not throw through the controller lifecycle.

## Resolution result

`ActionArgumentResolution` SHALL expose ordered arguments, named arguments, structured issues and a success indicator. Action invocation SHALL be prohibited when issues are present.

## Security

Issues SHALL not contain sensitive input values. Definitions MAY mark arguments as sensitive for later validation and observability layers. Body parse failures SHALL expose generic messages rather than decoder internals.

## Exclusions

I2 does not invoke actions, validate business rules, normalize action results, implement content negotiation or map issues to HTTP responses. Those concerns remain assigned to later WP-224 increments.
